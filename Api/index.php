<?php
//inclusão de arquivos
require 'vendor/autoload.php'
require 'ProdutosController.php'

// Importa classes do Slim
use Slim\Http\Request;
use Slim\Http\Response;

$app = new \Slim\App();

//Leitura dos produtos
$app->get('/produtos', 'ProdutosController:listagemProdutos');

//Novo produto
$app->post('/produtos', 'ProdutosController:criarProduto');

//Leitura
$app->get('/produtos/{id}', 'ProdutosController:buscarProduto');

//Atualiza produto
$app->put('/produtos/{id}', 'ProdutosController:atualizarProduto');

//Excluir produto
$app->delete('/produtos/{id}', 'ProdutosController:excluirProduto');

//Executa
$app->run();