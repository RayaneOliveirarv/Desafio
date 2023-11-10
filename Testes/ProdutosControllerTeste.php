<?php
require_once 'ProdutosController.php';

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class ProdutosControllerTest extends TestCase {
    private $controller;

    public function setUp(): void {
        // Configura conexão com o banco de dados 
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE produtos (id INTEGER PRIMARY KEY, nome TEXT, descricao TEXT, preco REAL, quantidade INTEGER)');

        // Inicializa o controlador com o PDO para testes
        $this->controller = new ProdutosController($pdo);
    }

    //Testa se a rota de listagem retorna um código
    public function testListarProdutos() {
        $request = ServerRequestFactory::createServerRequest('GET', '/produtos');
        $response = $this->controller->listarProdutos($request, (new ResponseFactory())->createResponse());

        $this->assertEquals(200, $response->getStatusCode());
        
    }

    //Testa se a rota de busca retorna um código
    public function testBuscarProdutoExistente() {
        // Inserir um produto de exemplo 
        $this->controller->criarProduto(ServerRequestFactory::createServerRequest('POST', '/produtos', [], http_build_query([
            'nome' => 'Produto de Teste',
            'descricao' => 'Descrição do produto de teste',
            'preco' => 19.99,
            'quantidade' => 50
        ])), (new ResponseFactory())->createResponse());

        // Consulta o produto 
        $request = ServerRequestFactory::createServerRequest('GET', '/produtos/1');
        $response = $this->controller->buscarProduto($request, (new ResponseFactory())->createResponse(), ['id' => 1]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    ////Testa se a rota busca produto que não existe
    public function testBuscarProdutoNaoExistente() {
        // Consultar um produto que não existe
        $request = ServerRequestFactory::createServerRequest('GET', '/produtos/1');
        $response = $this->controller->buscarProduto($request, (new ResponseFactory())->createResponse(), ['id' => 1]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    //Testa se a rota de criação retorna o código
    public function testCriarProduto() {
        $request = ServerRequestFactory::createServerRequest('POST', '/produtos', [], http_build_query([
            'nome' => 'Novo Produto',
            'descricao' => 'Descrição do novo produto',
            'preco' => 29.99,
            'quantidade' => 100
        ]));
        $response = $this->controller->criarProduto($request, (new ResponseFactory())->createResponse());

        $this->assertEquals(201, $response->getStatusCode());
    }

    //Testa se a rota atualização existe retorna um código
    public function testAtualizarProdutoExistente() {
        // Inserir um produto de exemplo para os testes
        $this->controller->criarProduto(ServerRequestFactory::createServerRequest('POST', '/produtos', [], http_build_query([
            'nome' => 'Produto de Teste',
            'descricao' => 'Descrição do produto de teste',
            'preco' => 19.99,
            'quantidade' => 50
        ])), (new ResponseFactory())->createResponse());

        // Atualizar o produto recém-inserido
        $request = ServerRequestFactory::createServerRequest('PUT', '/produtos/1', [], http_build_query([
            'nome' => 'Produto Atualizado',
            'descricao' => 'Descrição atualizada do produto',
            'preco' => 24.99,
            'quantidade' => 75
        ]));
        $response = $this->controller->atualizarProduto($request, (new ResponseFactory())->createResponse(), ['id' => 1]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    //Testa se a rota de atualização não existe retorna m código
    public function testAtualizarProdutoNaoExistente() {
        // Atualizar um produto que não existe
        $request = ServerRequestFactory::createServerRequest('PUT', '/produtos/1', [], http_build_query([
            'nome' => 'Produto Atualizado',
            'descricao' => 'Descrição atualizada do produto',
            'preco' => 24.99,
            'quantidade' => 75
        ]));
        $response = $this->controller->atualizarProduto($request, (new ResponseFactory())->createResponse(), ['id' => 1]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    //Testa se a rota de exclusão retorna um código
    public function testExcluirProdutoExistente() {
        // Inserir um produto de exemplo para teste
        $this->controller->criarProduto(ServerRequestFactory::createServerRequest('POST', '/produtos', [], http_build_query([
            'nome' => 'Produto de Teste',
            'descricao' => 'Descrição do produto de teste',
            'preco' => 19.99,
            'quantidade' => 50
        ])), (new ResponseFactory())->createResponse());

        // Excluir o produto recém-inserido
        $request = ServerRequestFactory::createServerRequest('DELETE', '/produtos/1');
        $response = $this->controller->excluirProduto($request, (new ResponseFactory())->createResponse(), ['id' => 1]);

        $this->assertEquals(200, $response->getStatusCode());
        
    }

    //Testa se arota de exclusão não existe retorna um código
    public function testExcluirProdutoNaoExistente() {
        // Excluir um produto que não existe
        $request = ServerRequestFactory::createServerRequest('DELETE', '/produtos/1');
        $response = $this->controller->excluirProduto($request, (new ResponseFactory())->createResponse(), ['id' => 1]);

        $this->assertEquals(404, $response->getStatusCode());
       
    }
}