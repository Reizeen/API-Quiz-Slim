<?php

require __DIR__ . '/../Models/Themes.php';
require __DIR__ . '/../Models/Questions.php';
require __DIR__ . '/../Models/Users.php';
require __DIR__ . '/../Models/Points.php';


/**
 * User Controller
 */
$app->get('/user/{id}', 'UserController:getUser');
$app->post('/signin', 'UserController:login');
$app->post('/signup', 'UserController:register');
$app->put('/user', 'UserController:changePassword');


/**
 * ThemeController
 */
$app->get('/themes', 'ThemeController:getThemes');


/**
 * QuestionController
 */
$app->get('/quiz/{theme}', 'QuestionController:gameQuiz');
$app->get('/questions/{id_user}', 'QuestionController:getQuestions');
$app->get('/question/{id}', 'QuestionController:getQuestion');
$app->post('/question', 'QuestionController:addQuestion');
$app->put('/question', 'QuestionController:setQuestion');


/**
 * PointController
 */
$app->get('/points', 'PointController:getPoints');
$app->get('/points/{id_user}', 'PointController:getPoint');
$app->put('/points', 'PointController:setPoints');


?>