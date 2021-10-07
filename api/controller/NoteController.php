<?php

namespace Api\Controller;

use System\Database as DB;

class NoteController {
    private $errorResponse;

    function index($id = 0) {
        $note = new \Note();
        $this->errorResponse = $this->getDefaultError();
        $id = intval($id);

        if ($id > 0) {
            $sql = 'SELECT * FROM `notes` WHERE `id` = :id';
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            if ($stmt->execute()) {
                $note = $stmt->rowCount() > 0 ? $stmt->fetch() : null;
                echo json_encode($this->getNoteFromDB($note));
                return;
            }
        } else {
            $this->errorResponse['error']['message'] .= '\nIdentificador da Nota é inválido.';
        }

        echo json_encode($this->errorResponse);
    }

    function list($page = 1, $limit = 5) {
        $this->errorResponse = $this->getDefaultError();
        $page = intval($page);
        $limit = intval($limit);

        $validPage = $page > 0;
        $validLimit = $limit == 5 || $limit == 10 || $limit == 20;
        $valid = $validPage && $validLimit;

        if (!$validPage) {
            $this->errorResponse['error']['message'] .= '\nNúmero de página inválido.';
        }

        if (!$validLimit) {
            $this->errorResponse['error']['message'] .= '\nNúmero máximo de notas inválido.';
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

        echo json_encode($this->errorResponse);
    }

    function remove($id = 0) {
        $this->errorResponse = $this->getDefaultError();
        $id = intval($id);

        if ($id > 0) {
            $sql = 'DELETE FROM `notes` WHERE `id` = :id';
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return;
            }
        }
        echo json_encode($this->errorResponse);
    }

    function create($params) {
        $this->errorResponse = $this->getDefaultError();
        $title = $this->sanitizeString($this->getValueFromArray('titulo', $_POST));
        $content = $this->sanitizeString($this->getValueFromArray('conteudo', $_POST));

        if ($title) {
            $note = new \Note();
            $note->setTitle($title);
            $note->setContent($content);

            $sql = 'INSERT INTO `notes` SET `title` = :title, `content` = :content';
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->bindValue(':title', $note->getTitle(), \PDO::PARAM_STR);
            $stmt->bindValue(':content', $note->getContent(), \PDO::PARAM_STR);
            if ($stmt->execute()) {
                return;
            }
        } else {
            $this->errorResponse['error']['message'] = 'Título da nota é obrigatório.';
        }

        echo json_encode($this->errorResponse);
    }

    private function getDefaultError(): array {
        return ['error' => ['message' => 'Não foi possível concluir sua requisição.']];
    }

    private function stripString(?string $str): ?string {
        if ($str) {
            $str = htmlspecialchars_decode($str);
            return stripslashes($str);
        }
        return null;
    }
    
    private function sanitizeString(?string $str): ?string {
        if ($str) {
            $str = trim($str);
            $str = htmlspecialchars($str);
            return addslashes($str);
        }
        return null;
    }
    
    private function getValueFromArray($key, array $array, $defaultValue = null) {
        if ($key && $array) {
            return array_key_exists($key, $array) ? $array[$key] : $defaultValue;
        }
        return $defaultValue;
    }
    
    private function getNoteFromDB(?array $result): ?\Note {
        $note = null;
    
        if ($result) {
            $note = new \Note();
            $note->setId($this->getValueFromArray('id', $result));
            $note->setTitle($this->stripString($this->getValueFromArray('title', $result)));
            $note->setContent($this->getValueFromArray('content', $result));
        
            $format = 'Y-m-d H:m';
            $created = strtotime( date($format, strtotime($this->getValueFromArray('created_date', $result))) );
            $updated = strtotime( date($format, strtotime($this->getValueFromArray('updated_date', $result))) );
            $note->setCreatedDate($created);
            $note->setUpdatedDate($updated);
        }
    
        return $note;
    }

}