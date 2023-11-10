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
    }

}