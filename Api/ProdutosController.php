<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProdutosController 
{
//CRUDs
    private $db;

    //interação com o banco
    public function __construct(PDO $db) {
        $this->db = $db;
    

    public function listagemProdutos(Request $request, Response $response) {
        $pagina = $request->getQueryParam('pagina', 1);
        //registros por página
        $registrosPorPagina = 5;
        //registros pulados página atual
        $offset = ($pagina - 1) * $registrosPorPagina;

        // Obter produtos do banco
        $stmt = $this->db->prepare("SELECT * FROM produtos LIMIT :offset, :limit");
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $registrosPorPagina, PDO::PARAM_INT);
        $stmt->execute();

        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Contagem do total de produtos
        $totalRegistros = $this->db->query("SELECT COUNT(*) FROM produtos")->fetchColumn();
        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

        //Array com as informações 
        $resposta = [
            'pagina_atual' => $pagina,
            'total_paginas' => $totalPaginas,
            'total_registros' => $totalRegistros,
            'registros_por_pagina' => $registrosPorPagina,
            'registros' => $produtos
        ];

        //retorno da resposta
        return $response->withJson($resposta);
    }

    public function buscarProduto(Request $request, Response $response, array $args) {
        $id = $args['id'];

        // Busca de produto pelo ID
        $stmt = $this->db->prepare("SELECT * FROM produtos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        //Verificação se o produto foi encontrado
        if (!$produto) {
            return $response->withJson(['mensagem' => 'Produto não encontrado'], 404);
        }

        return $response->withJson($produto);
    }

    public function criarProduto(Request $request, Response $response) {
        $dados = $request->getParsedBody();

        

}