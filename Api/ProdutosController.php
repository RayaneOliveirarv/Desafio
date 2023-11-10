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

        //Validação dos dados recebidos
        if (empty($dados['nome']) || empty($dados['preco']) || empty($dados['quantidade'])) {
            return $response->withJson(['mensagem' => 'Todos os campos são obrigatórios'], 400);
        }
    
        if (!is_numeric($dados['preco']) || !is_numeric($dados['quantidade']) || $dados['preco'] < 0 || $dados['quantidade'] < 0) {
            return $response->withJson(['mensagem' => 'Preço e quantidade devem ser números positivos'], 400);
        }

        // Consulta para inserir novo produto no banco de dados
        $stmt = $this->db->prepare("INSERT INTO produtos (nome, descricao, preco, quantidade) VALUES (:nome, :descricao, :preco, :quantidade)");
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':descricao', $dados['descricao']);
        $stmt->bindParam(':preco', $dados['preco']);
        $stmt->bindParam(':quantidade', $dados['quantidade']);
        $stmt->execute();

        //Obter id do produto criado
        $novoProdutoId = $this->db->lastInsertId();

        // Consulta para obter o produto criado
        $stmt = $this->db->prepare("SELECT * FROM produtos WHERE id = :id");
        $stmt->bindParam(':id', $novoProdutoId, PDO::PARAM_INT);
        $stmt->execute();

        $novoProduto = $stmt->fetch(PDO::FETCH_ASSOC);

        return $response->withJson($novoProduto, 201);
    }

    public function atualizarProduto(Request $request, Response $response, array $args) {
        $id = $args['id'];
        $dados = $request->getParsedBody();

        // Validação dos dados recebidos
        //Verificar se os campos estão presentes nos dados recebidos
        if (empty($dados['nome']) || empty($dados['preco']) || empty($dados['quantidade'])) {
            return $response->withJson(['mensagem' => 'Todos os campos são obrigatórios'], 400);
        }

        //Verifica se campos são positivos
        if (!is_numeric($dados['preco']) || !is_numeric($dados['quantidade']) || $dados['preco'] < 0 || $dados['quantidade'] < 0) {
            return $response->withJson(['mensagem' => 'Preço e quantidade devem ser números positivos'], 400);
        }

        // Verifica se id existe no banco
        $stmt = $this->db->prepare("SELECT * FROM produtos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $produtoExistente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        //Se o produto é válido, atualiza o produto
        if (!$produtoExistente) {
            return $response->withJson(['mensagem' => 'Produto não encontrado'], 404);
        }

        // Atualizar o produto no banco de dados com base no id fornecido
        $stmt = $this->db->prepare("UPDATE produtos SET nome = :nome, descricao = :descricao, preco = :preco, quantidade = :quantidade WHERE id = :id");
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':descricao', $dados['descricao']);
        $stmt->bindParam(':preco', $dados['preco']);
        $stmt->bindParam(':quantidade', $dados['quantidade']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Obter o produto atualizado
        $stmt = $this->db->prepare("SELECT * FROM produtos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $produtoAtualizado = $stmt->fetch(PDO::FETCH_ASSOC);

        //Se não for encontrado retorna mensagem de erro
        if (!$produtoAtualizado) {
            return $response->withJson(['mensagem' => 'Produto não encontrado'], 404);
        }

        return $response->withJson($produtoAtualizado);
    }

    public function excluirProduto(Request $request, Response $response, array $args) {
        $id = $args['id'];

        // Consulta SQL para excluir o produto por id
        $stmt = $this->db->prepare("DELETE FROM produtos WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        //Verificando o nº de linhas para garantir exclusão
        $linhasAfetadas = $stmt->rowCount();

        //Se o produto não for encontrado, retorna resposta
        if ($linhasAfetadas === 0) {
            return $response->withJson(['mensagem' => 'Produto não encontrado'], 404);
        }

        return $response->withJson(['mensagem' => 'Produto excluído com sucesso']);
    }
}
}