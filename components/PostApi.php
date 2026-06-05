<?php

namespace app\components;

use yii\base\Component;

class PostApi extends Component
{
    public string $baseUrl = 'https://api.blogcuavinh.id.vn';

    public function getPosts(): array
    {
        $url = $this->baseUrl . '/posts';

        $response = file_get_contents($url);

        if ($response === false) {
            throw new \RuntimeException('Cannot call post API');
        }

        $data = json_decode($response, true);

        return $data['data']['posts'] ?? [];
    }
}
