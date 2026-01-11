<?php
require_once __DIR__ . '/vendor/autoload.php';

$faker = Faker\Factory::create('fr_FR'); // locale FR, optionnel
//$faker->seed(1234); // optionnel : résultats reproductibles

echo $faker->name() . PHP_EOL;
echo $faker->email() . PHP_EOL;
echo $faker->text(120) . PHP_EOL;

// Modifiers utiles :
$uniqueDigit = $faker->unique()->randomDigit(); // valeur unique
$maybePhone  = $faker->optional(0.7, '—')->phoneNumber(); // 30% de chances de NULL (ici '—')
$evenOnly    = $faker->valid(fn($n) => $n % 2 === 0)->numberBetween(0, 20);

echo "uniqueDigit=$uniqueDigit | maybePhone=$maybePhone | evenOnly=$evenOnly" . PHP_EOL;
