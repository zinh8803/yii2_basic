<?php

namespace app\commands;

use Faker\Factory;
use Yii;
use yii\console\Controller;

class SeedController extends Controller
{
    public function actionProducts($count = 100000): void
    {
        $faker = Factory::create();
        $batchSize = 5000;
        $rows = [];
        $start = microtime(true);
        $now = time();

        $categoryIds = Yii::$app->db
            ->createCommand('SELECT id FROM categories')
            ->queryColumn();

        $brandIds = Yii::$app->db
            ->createCommand('SELECT id FROM brands')
            ->queryColumn();

        if (empty($categoryIds)) {
            $this->stderr("No categories found.\n");
            return;
        }

        if (empty($brandIds)) {
            $this->stderr("No brands found.\n");
            return;
        }

        $currentMaxId = (int) Yii::$app->db
            ->createCommand('SELECT MAX(id) FROM products')
            ->queryScalar();

        $startIndex = $currentMaxId + 1;
        $endIndex = $startIndex + $count - 1;

        for ($i = $startIndex; $i <= $endIndex; $i++) {
            $rows[] = [
                'Fake product ' . $i,
                'fake-product-' . $i,
                $categoryIds[array_rand($categoryIds)],
                $brandIds[array_rand($brandIds)],
                $faker->sentence(10),
                rand(0, 1),
                $now,
                $now,
            ];

            if (count($rows) >= $batchSize) {
                $this->insertProducts($rows);
                $this->stdout("Inserted: {$i}\n");
                $rows = [];
            }
        }

        if (!empty($rows)) {
            $this->insertProducts($rows);
        }

        $end = microtime(true);

        $this->stdout("Done in " . round($end - $start, 2) . "s\n");
    }

    private function insertProducts(array $rows): void
    {
        Yii::$app->db->createCommand()
            ->batchInsert(
                'products',
                [
                    'name',
                    'slug',
                    'category_id',
                    'brand_id',
                    'description',
                    'status',
                    'created_at',
                    'updated_at',
                ],
                $rows
            )->execute();
    }
}
