<?php

use App\Middleware\AuthMiddleware;

require __DIR__ . '/../Models/Themes.php';
require __DIR__ . '/../Models/Questions.php';
require __DIR__ . '/../Models/Users.php';
require __DIR__ . '/../Models/Points.php';
require __DIR__ . '/../Models/Reports.php';


/**
 * User Controller
 */
$app->post('/signin', 'UserController:login');
$app->post('/signup', 'UserController:register');
$app->post('/session', 'UserController:session');

$app->group('', function() use($app){
    $app->get('/user/{id}', 'UserController:getUser');
    $app->put('/user', 'UserController:changePassword');
})->add(new AuthMiddleware($container));


/**
 * ThemeController
 */
$app->get('/themes', 'ThemeController:getThemes')->add(new AuthMiddleware($container));


/**
 * QuestionController
 */
$app->group('', function() use($app){
    $app->get('/quiz/{theme}', 'QuestionController:gameQuiz');
    $app->get('/questions/{id_user}', 'QuestionController:getQuestions');
    $app->get('/question/{id}', 'QuestionController:getQuestion');
    $app->post('/question', 'QuestionController:addQuestion');
    $app->put('/question', 'QuestionController:setQuestion');
    $app->delete('/question/{id}', 'QuestionController:deleteQuestion');
})->add(new AuthMiddleware($container));


/**
 * PointController
 */
$app->group('', function() use($app){
    $app->get('/points', 'PointController:getPoints');
    $app->get('/points/{id_user}', 'PointController:getPoint');
    $app->put('/points', 'PointController:setPoints');
})->add(new AuthMiddleware($container));

/**
 * ReportController
 */
$app->post('/report', 'ReportController:setReport')->add(new AuthMiddleware($container));

?>