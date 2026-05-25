<?php

namespace app\controllers;

use ReflectionClass;
use ReflectionMethod;
use Yii;
use yii\base\Model;
use yii\web\Controller;
use yii\web\Response;

class SwaggerController extends Controller
{
    private const RESTFUL_ACTION_METHODS = [
        'index' => ['get'],
        'view' => ['get'],
        'create' => ['post'],
        'update' => ['put', 'patch'],
        'delete' => ['delete'],
    ];

    public function actionIndex(): string
    {
        Yii::$app->response->format = Response::FORMAT_HTML;

        $openApiUrl = rtrim(Yii::$app->request->baseUrl, '/') . '/swagger-json';

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>API Swagger</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
        body { margin: 0; background: #f7f7f7; }
        #swagger-ui { max-width: 1460px; margin: 0 auto; }
    </style>
</head>
<body>
<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
<script>
    window.ui = SwaggerUIBundle({
        url: "{$openApiUrl}",
        dom_id: "#swagger-ui",
        deepLinking: true,
        presets: [SwaggerUIBundle.presets.apis],
        layout: "BaseLayout"
    });
</script>
</body>
</html>
HTML;
    }

    public function actionJson(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Yii2 App API',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => Yii::$app->request->hostInfo . Yii::$app->request->baseUrl],
            ],
            'paths' => $this->buildPaths(),
            'components' => [
                'schemas' => [
                    'ApiResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'boolean'],
                            'data' => ['nullable' => true],
                            'message' => ['type' => 'string'],
                            'code' => ['type' => 'integer'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildPaths(): array
    {
        $paths = [];
        $controllerFiles = glob(Yii::getAlias('@app/controllers/*Controller.php')) ?: [];

        foreach ($controllerFiles as $file) {
            $class = 'app\\controllers\\' . basename($file, '.php');
            if (
                !class_exists($class)
                || in_array($class, [BaseController::class, self::class], true)
                || !is_subclass_of($class, Controller::class)
            ) {
                continue;
            }

            $controllerId = $this->controllerIdFromClass($class);
            $reflection = new ReflectionClass($class);
            $uses = $this->extractUses((string) file_get_contents($file));

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (!$this->isActionMethod($method, $class)) {
                    continue;
                }

                $actionId = $this->actionIdFromMethod($method->getName());
                $path = $this->pathForAction($controllerId, $actionId);
                $formClass = $this->detectFormClass($method, $uses);

                foreach ($this->httpMethodsForAction($actionId, $class) as $httpMethod) {
                    $paths[$path][$httpMethod] = [
                        'tags' => [$controllerId],
                        'summary' => $this->summary($controllerId, $actionId),
                        'operationId' => $controllerId . '-' . $actionId . '-' . $httpMethod,
                        'parameters' => $this->parametersForAction($method, $actionId),
                        'requestBody' => $this->requestBodyForAction($httpMethod, $formClass),
                        'responses' => $this->responses(),
                    ];

                    if ($paths[$path][$httpMethod]['requestBody'] === null) {
                        unset($paths[$path][$httpMethod]['requestBody']);
                    }
                }
            }
        }

        ksort($paths);

        return $paths;
    }

    private function isActionMethod(ReflectionMethod $method, string $class): bool
    {
        return $method->class === $class
            && str_starts_with($method->getName(), 'action')
            && $method->getName() !== 'actions';
    }

    private function controllerIdFromClass(string $class): string
    {
        $short = substr(strrchr($class, '\\') ?: $class, 1);
        $name = preg_replace('/Controller$/', '', $short);

        return $this->camelToKebab((string) $name);
    }

    private function actionIdFromMethod(string $method): string
    {
        return $this->camelToKebab(substr($method, 6));
    }

    private function camelToKebab(string $value): string
    {
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $value));
    }

    private function pathForAction(string $controllerId, string $actionId): string
    {
        if ($actionId === 'index' || $actionId === 'create') {
            return '/' . $controllerId;
        }

        if (in_array($actionId, ['view', 'update', 'delete'], true)) {
            return '/' . $controllerId . '/{id}';
        }

        return '/' . $controllerId . '/' . $actionId;
    }

    private function httpMethodsForAction(string $actionId, string $class): array
    {
        if (isset(self::RESTFUL_ACTION_METHODS[$actionId])) {
            return self::RESTFUL_ACTION_METHODS[$actionId];
        }

        $verbFilters = $this->verbFilters($class);
        if (isset($verbFilters[$actionId])) {
            return array_map('strtolower', $verbFilters[$actionId]);
        }

        if (preg_match('/^(login|logout|contact|add|remove|clear|change|update|create)/', $actionId)) {
            return ['post'];
        }

        return ['get'];
    }

    private function verbFilters(string $class): array
    {
        try {
            $reflection = new ReflectionClass($class);
            if (!$reflection->hasMethod('behaviors')) {
                return [];
            }

            $method = $reflection->getMethod('behaviors');
            $source = $this->methodSource($method);
            preg_match_all("/'([^']+)'\\s*=>\\s*\\[([^\\]]+)\\]/", $source, $matches, PREG_SET_ORDER);

            $filters = [];
            foreach ($matches as $match) {
                preg_match_all("/'([^']+)'/", $match[2], $verbs);
                if (!empty($verbs[1])) {
                    $filters[$match[1]] = $verbs[1];
                }
            }

            return $filters;
        } catch (\Throwable) {
            return [];
        }
    }

    private function parametersForAction(ReflectionMethod $method, string $actionId): array
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $in = in_array($actionId, ['view', 'update', 'delete'], true) && $parameter->getName() === 'id'
                ? 'path'
                : 'query';

            $parameters[] = [
                'name' => $parameter->getName(),
                'in' => $in,
                'required' => $in === 'path' || !$parameter->isOptional(),
                'schema' => $this->schemaFromName($parameter->getName()),
            ];
        }

        if ($actionId === 'index') {
            $parameters[] = ['name' => 'page', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer']];
            $parameters[] = ['name' => 'limit', 'in' => 'query', 'required' => false, 'schema' => ['type' => 'integer']];
        }

        return $parameters;
    }

    private function requestBodyForAction(string $httpMethod, ?string $formClass): ?array
    {
        if (!in_array($httpMethod, ['post', 'put', 'patch'], true)) {
            return null;
        }

        $schema = $formClass !== null ? $this->schemaForForm($formClass) : [
            'type' => 'object',
            'additionalProperties' => true,
        ];
        $content = $this->schemaHasBinary($schema)
            ? [
                'multipart/form-data' => ['schema' => $schema],
                'application/json' => ['schema' => $schema],
            ]
            : [
                'application/json' => ['schema' => $schema],
                'multipart/form-data' => ['schema' => $schema],
            ];

        return [
            'required' => true,
            'content' => $content,
        ];
    }

    private function schemaForForm(string $formClass): array
    {
        if (!class_exists($formClass) || !is_subclass_of($formClass, Model::class)) {
            return ['type' => 'object', 'additionalProperties' => true];
        }

        try {
            /** @var Model $form */
            $form = new $formClass();
            $required = [];
            $properties = [];

            foreach ($form->rules() as $rule) {
                $attributes = (array) ($rule[0] ?? []);
                $validator = $rule[1] ?? null;

                foreach ($attributes as $attribute) {
                    $properties[$attribute] ??= $this->schemaFromName((string) $attribute);
                    if ($validator === 'file') {
                        $properties[$attribute] = $this->fileSchemaForRule((string) $attribute, $rule);
                    }
                    if ($validator === 'required') {
                        $required[] = $attribute;
                    }
                    if ($validator === 'integer') {
                        $properties[$attribute]['type'] = 'integer';
                    }
                    if (in_array($validator, ['number', 'double'], true)) {
                        $properties[$attribute]['type'] = 'number';
                    }
                    if ($validator === 'boolean') {
                        $properties[$attribute]['type'] = 'boolean';
                    }
                    if ($validator === 'each') {
                        $properties[$attribute] = ['type' => 'array', 'items' => ['type' => 'string']];
                    }
                }
            }

            return array_filter([
                'type' => 'object',
                'properties' => $properties,
                'required' => array_values(array_unique($required)),
            ]);
        } catch (\Throwable) {
            return ['type' => 'object', 'additionalProperties' => true];
        }
    }

    private function fileSchemaForRule(string $attribute, array $rule): array
    {
        if (($rule['maxFiles'] ?? 1) > 1 || str_ends_with($attribute, 'Files') || str_ends_with($attribute, 'files')) {
            return [
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                    'format' => 'binary',
                ],
            ];
        }

        return [
            'type' => 'string',
            'format' => 'binary',
        ];
    }

    private function schemaHasBinary(array $schema): bool
    {
        foreach (($schema['properties'] ?? []) as $property) {
            if (($property['format'] ?? null) === 'binary') {
                return true;
            }

            if (($property['type'] ?? null) === 'array' && (($property['items']['format'] ?? null) === 'binary')) {
                return true;
            }
        }

        return false;
    }

    private function detectFormClass(ReflectionMethod $method, array $uses): ?string
    {
        $source = $this->methodSource($method);
        if (!preg_match('/new\\s+([A-Za-z_][A-Za-z0-9_\\\\]*Form)\\s*\\(/', $source, $match)) {
            return null;
        }

        $name = $match[1];
        if (str_contains($name, '\\')) {
            return ltrim($name, '\\');
        }

        return $uses[$name] ?? null;
    }

    private function extractUses(string $source): array
    {
        preg_match_all('/^use\\s+([^;]+);/m', $source, $matches);

        $uses = [];
        foreach ($matches[1] as $fqcn) {
            $parts = explode('\\', trim($fqcn));
            $uses[end($parts)] = trim($fqcn);
        }

        return $uses;
    }

    private function methodSource(ReflectionMethod $method): string
    {
        $file = (string) $method->getFileName();
        $lines = file($file) ?: [];
        $length = $method->getEndLine() - $method->getStartLine() + 1;

        return implode('', array_slice($lines, $method->getStartLine() - 1, $length));
    }

    private function typeFromName(string $name): string
    {
        if (preg_match('/(^id$|_id$|count|quantity|status|page|limit|stock|sort|rating)/', $name)) {
            return 'integer';
        }

        if (preg_match('/(price|total|amount|fee|discount|weight|value)/', $name)) {
            return 'number';
        }

        if (preg_match('/(^is_|_ids$|items$|products$|files$)/', $name)) {
            return str_ends_with($name, 's') || str_ends_with($name, '_ids') ? 'array' : 'boolean';
        }

        return 'string';
    }

    private function schemaFromName(string $name): array
    {
        $type = $this->typeFromName($name);

        if ($type === 'array') {
            return ['type' => 'array', 'items' => ['type' => 'string']];
        }

        return ['type' => $type];
    }

    private function responses(): array
    {
        return [
            '200' => [
                'description' => 'Successful response',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ApiResponse'],
                    ],
                ],
            ],
            '400' => ['description' => 'Bad request'],
            '404' => ['description' => 'Not found'],
            '422' => ['description' => 'Validation failed'],
            '500' => ['description' => 'Internal server error'],
        ];
    }

    private function summary(string $controllerId, string $actionId): string
    {
        return ucwords(str_replace('-', ' ', $actionId . ' ' . $controllerId));
    }
}
