<?php

use System\Database as DB;

$router->get('/ping', function($params = []) {
    echo json_encode("PONG!");
});

$router->get('/', function() {
    $note = null;
    $id = intval(getValueFromArray('idNota', $_GET));

    if ($id > 0) {
        $sql = 'SELECT * FROM `notes` WHERE `id` = :id';
        $stmt = DB::getConnection()->prepare($sql);
        
        // retorna um objeto da classe especificada | executar o construtor antes de instanciar os campos
        //$stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Note');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch();
        }

        $note = getNoteFromDB($result);
    }
    echo json_encode($note);
});

$router->get('/list', function() {
    $error = ['error' => ['message' => '']];
    $page = getValueFromArray('paginaAtual', $_GET, 1);
    $limit = getValueFromArray('maxNotas', $_GET, 5);

    $page = intval($page);
    $limit = intval($limit);

    $validPage = $page > 0;
    $validLimit = $limit == 5 || $limit == 10 || $limit == 20;
    $valid = $validPage && $validLimit;

    if (!$validPage) {
        $error['error']['message'] .= 'Número de página inválido.';
    }

    if (!$validLimit) {
        $error['error']['message'] .= ' .Número máximo de notas inválido.';
    }

    if (!$valid) {
        echo json_encode($error);
        return;
    }

    $offset = ($page - 1) * $limit;

    $list = [];
    $sql = 'SELECT * FROM `notes` LIMIT :offset, :limit';
    $stmt = DB::getConnection()->prepare($sql);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $array = [];
    $rows = $stmt->rowCount();

    if ($rows == 1) {
        $array = $stmt->fetch();
        $list[] = getNoteFromDB($array);
    } elseif ($rows > 1) {
        $array = $stmt->fetchAll();
        foreach ($array as $note) {
            $list[] = getNoteFromDB($note);
        }
    }

    echo json_encode($list);
});

$router->post('/', function() {
    $response = 'Erro de validação. Informe o Título.';
    $title = sanitizeString(getValueFromArray('titulo', $_POST));
    $content = sanitizeString(getValueFromArray('conteudo', $_POST));

    if ($title) {
        $note = new Note();
        $note->setTitle($title);
        $note->setContent($content);

        // salvar
        $sql = 'INSERT INTO `notes` SET `title` = :title, `content` = :content';
        $stmt = DB::getConnection()->prepare($sql);
        $stmt->bindValue(':title', $note->getTitle(), PDO::PARAM_STRING);
        $stmt->bindValue(':content', $note->getContent(), PDO::PARAM_STRING);
        $stmt->execute();

        echo json_encode('');
        return;
    }
    echo $response;
});

$router->put('/test', function() {
    echo json_encode("Hi put");
});

function stripString(?string $str) {
    if ($str) {
        $str = htmlspecialchars_decode($str);
        return stripslashes($str);
    }
    return null;
}

function sanitizeString(?string $str) {
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

function getNoteFromDB(?array $result) {
    $note = new Note();

    if ($result) {
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