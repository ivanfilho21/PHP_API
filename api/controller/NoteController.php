<?php

namespace Api\Controller;

use System\Database as DB;
use System\Utils as Utils;

class NoteController {
    private $errorResponse;

    function index($idNota = 0) {
        $note = new \Note();
        $this->errorResponse = $this->getDefaultError();
        $id = intval($idNota);

        if ($id > 0) {
            $sql = 'SELECT * FROM `notes` WHERE `id` = :id';
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            if ($stmt->execute()) {
                $note = $stmt->rowCount() > 0 ? $stmt->fetch() : null;

                http_response_code(200);
                echo json_encode($this->getNoteFromDB($note));
                return;
            }
        } else {
            $this->errorResponse['error']['message'] .= "\nIdentificador da Nota é inválido.";
        }

        http_response_code(400);
        echo json_encode($this->errorResponse);
    }

    function updateNote($idNota, $params) {
        $note = new \Note();
        $this->errorResponse = $this->getDefaultError();
        $id = intval($idNota);

        if ($id > 0) {
            $title = Utils::sanitizeString(Utils::getFromArray('titulo', $params));
            $content = Utils::sanitizeString(Utils::getFromArray('conteudo', $params));

            if ($title) {
                $note->setId($id);
                $note->setTitle($title);
                $note->setContent($content);
    
                if ($this->upsertNote($note)) {
                    http_response_code(204);
                    return;
                }
            } else {
                $this->errorResponse['error']['message'] .= "\nTítulo da nota é obrigatório.";
            }
        } else {
            $this->errorResponse['error']['message'] .= "\nIdentificador da Nota é inválido.";
        }

        http_response_code(400);
        echo json_encode($this->errorResponse);
    }

    function update($idNota = 0) {
        return $this->updateNote($idNota, $this->getRequestParams());
    }

    function remove($idNota = 0) {
        $this->errorResponse = $this->getDefaultError();
        $id = intval($idNota);

        if ($id > 0) {
            $sql = 'DELETE FROM `notes` WHERE `id` = :id';
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                http_response_code(204);
                return;
            }
        }

        http_response_code(400);
        echo json_encode($this->errorResponse);
    }

    function create($params) {
        $this->errorResponse = $this->getDefaultError();
        $title = Utils::sanitizeString(Utils::getFromArray('titulo', $params));
        $content = Utils::sanitizeString(Utils::getFromArray('conteudo', $params));

        if ($title) {
            $note = new \Note();
            $note->setTitle($title);
            $note->setContent($content);

            if ($this->upsertNote($note)) {
                http_response_code(204);
                return;
            }
        } else {
            $this->errorResponse['error']['message'] .= "\nTítulo da nota é obrigatório.";
        }

        http_response_code(400);
        echo json_encode($this->errorResponse);
    }

    function list($paginaAtual = 1, $maxNotas = 5) {
        $this->errorResponse = $this->getDefaultError();
        
        $paginaAtual = empty($paginaAtual) ? 1 : $paginaAtual;
        $maxNotas = empty($maxNotas) ? 5 : $maxNotas;

        $page = intval($paginaAtual);
        $limit = intval($maxNotas);

        $validPage = $page > 0;
        $validLimit = $limit == 5 || $limit == 10 || $limit == 20;
        $valid = $validPage && $validLimit;

        if (!$validPage) {
            $this->errorResponse['error']['message'] .= "\nNúmero de página inválido.";
        }

        if (!$validLimit) {
            $this->errorResponse['error']['message'] .= "\nNúmero máximo de notas inválido.";
        }

        if ($valid) {
            $offset = ($page - 1) * $limit;
            $sql = 'SELECT * FROM `notes` LIMIT :offset, :limit';
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        
            if ($stmt->execute()) {
                $list = [];
                $result = [];
                $rows = $stmt->rowCount();
            
                if ($rows == 1) {
                    $result = $stmt->fetch();
                    $list[] = $this->getNoteFromDB($result);
                } elseif ($rows > 1) {
                    $result = $stmt->fetchAll();
                    foreach ($result as $note) {
                        $list[] = $this->getNoteFromDB($note);
                    }
                }

                echo json_encode($list);
                return;
            }
        }

        http_response_code(400);
        echo json_encode($this->errorResponse);
    }

    /**
     * Retorna os parâmetros da requisição PUT.
     */
    private function getRequestParams(): array {
        $array = explode('&', file_get_contents("php://input"));
        $params = [];

        foreach ($array as $line) {
            $entry = explode('=', $line);

            if (count($entry) == 1) {
                continue;
            }

            $key = Utils::getFromArray(0, $entry);
            $value = Utils::getFromArray(1, $entry);

            if ($key) {
                $key = urldecode($key);
                $value = $value ? urldecode($value) : $value;
                $params[$key] = $value;
            }
        }

        return $params;
    }

    private function getDefaultError(): array {
        return ['error' => ['message' => 'Não foi possível concluir sua requisição.']];
    }
    
    private function getNoteFromDB(?array $result): ?\Note {
        $note = null;
    
        if ($result) {
            $note = new \Note();
            $note->setId(Utils::getFromArray('id', $result));
            $note->setTitle(Utils::stripString(Utils::getFromArray('title', $result)));
            $note->setContent(Utils::getFromArray('content', $result));
        
            $format = 'Y-m-d H:m';
            $created = strtotime( date($format, strtotime(Utils::getFromArray('created_date', $result))) );
            $updated = strtotime( date($format, strtotime(Utils::getFromArray('updated_date', $result))) );
            $note->setCreatedDate($created);
            $note->setUpdatedDate($updated);
        }
    
        return $note;
    }

    private function upsertNote(\Note $note): bool {
        $updateMode = !empty($note->getId());
        $operation = $updateMode ? 'UPDATE' : 'INSERT INTO';
        $where = $updateMode ? ' WHERE `id` = :id' : '';
        $sql = "$operation `notes` SET `title` = :title, `content` = :content$where";
        $stmt = DB::getConnection()->prepare($sql);
        if ($updateMode) {
            $stmt->bindValue(':id', $note->getId(), \PDO::PARAM_INT);
        }
        $stmt->bindValue(':title', $note->getTitle(), \PDO::PARAM_STR);
        $stmt->bindValue(':content', $note->getContent(), \PDO::PARAM_STR);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

}