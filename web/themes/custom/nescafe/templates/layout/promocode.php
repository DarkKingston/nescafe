<?php

require __DIR__ . '/vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);
$promoValue = 'asdafdfsfsa';

echo $twig->render('page--account.html.twig', ['promo' => $promoValue]);
