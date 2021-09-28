<?php

use System\Database as DB;

$router->get('/ping', function() {
    echo json_encode("PONG!");
});

$router->get('/', function() {
    $error = ['error' => ['message' => 'Não foi possível concluir sua requisição.']];
    $note = null;
    $id = intval(getValueFromArray('idNota', $_GET));

    if ($id > 0) {
        $sql = 'SELECT * FROM `notes` WHERE `id` = :id';
        $stmt = DB::getConnection()->prepare($sql);
        
        // retorna um objeto da classe especificada | executar o construtor antes de instanciar os campos
        //$stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Note');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $note = $stmt->rowCount() > 0 ? $stmt->fetch() : null;
            echo json_encode(getNoteFromDB($note));
            return;
        }
    }

    echo json_encode($error);
});

$router->get('/list', function() {
    $error = ['error' => ['message' => 'Não foi possível concluir sua requisição.']];
    $page = getValueFromArray('paginaAtual', $_GET, 1);
    $limit = getValueFromArray('maxNotas', $_GET, 5);

    $page = intval($page);
    $limit = intval($limit);

    $validPage = $page > 0;
    $validLimit = $limit == 5 || $limit == 10 || $limit == 20;
    $valid = $validPage && $validLimit;

    if (!$validPage) {
        $error['error']['message'] .= '\nNúmero de página inválido.';
    }

    if (!$validLimit) {
        $error['error']['message'] .= '\nNúmero máximo de notas inválido.';
    }

    if ($valid) {
        $offset = ($page - 1) * $limit;
        $sql = 'SELECT * FROM `notes` LIMIT :offset, :limit';
        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    
        if ($stmt->execute()) {
            $list = [];
            $result = [];
            $rows = $stmt->rowCount();
        
            if ($rows == 1) {
                $result = $stmt->fetch();
                $list[] = getNoteFromDB($result);
            } elseif ($rows > 1) {
                $result = $stmt->fetchAll();
                foreach ($result as $note) {
                    $list[] = getNoteFromDB($note);
                }
            }
    
            echo json_encode($list);
            return;
        }
    }

    echo json_encode($error);
});

$router->post('/', function() {
    $error = ['error' => ['message' => 'Não foi possível concluir sua requisição.']];
    $title = sanitizeString(getValueFromArray('titulo', $_POST));
    $content = sanitizeString(getValueFromArray('conteudo', $_POST));

    if ($title) {
        $note = new Note();
        $note->setTitle($title);
        $note->setContent($content);

        // salvar
        $sql = 'INSERT INTO `notes` SET `title` = :title, `content` = :content';
        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindValue(':title', $note->getTitle(), PDO::PARAM_STR);
        $stmt->bindValue(':content', $note->getContent(), PDO::PARAM_STR);
        if ($stmt->execute()) {
            return;
        }
    } else {
        $error['error']['message'] = 'Título da nota é obrigatório.';
    }

    echo json_encode($error);
});

$router->delete('/', function() {
    $error = ['error' => ['message' => 'Não foi possível concluir sua requisição.']];
    $id = intval(getValueFromArray('idNota', $_GET));

    if ($id > 0) {
        $sql = 'DELETE FROM `notes` WHERE `id` = :id';
        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return;
        }
    }
    echo json_encode($error);
});

function stripString(?string $str): ?string {
    if ($str) {
        $str = htmlspecialchars_decode($str);
        return stripslashes($str);
    }
    return null;
}

function sanitizeString(?string $str): ?string {
    if ($str) {
        $str = trim($str);
        $str = htmlspecialchars($str);
        return addslashes($str);
    }
    return null;
}

function getValueFromArray($key, array $array, $defaultValue = null) {
    if ($key && $array) {
        return array_key_exists($key, $array) ? $array[$key] : $defaultValue;
    }
    return $defaultValue;
}

function getNoteFromDB(?array $result): ?Note {
    $note = null;

    if ($result) {
        $note = new Note();
        $note->setId(getValueFromArray('id', $result));
        $note->setTitle(stripString(getValueFromArray('title', $result)));
        $note->setContent(getValueFromArray('content', $result));
    
        $format = 'Y-m-d H:m';
        $created = strtotime( date($format, strtotime(getValueFromArray('created_date', $result))) );
        $updated = strtotime( date($format, strtotime(getValueFromArray('updated_date', $result))) );
        $note->setCreatedDate($created);
        $note->setUpdatedDate($updated);
    }

    return $note;
}