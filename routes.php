<?php

use System\Database as DB;

$router->get('/ping', function($params = []) {
    echo json_encode($params);
}, ['params' => ['name', 'id']]);



$router->get('/', function($id = 0) {
    $note = null;
    if ($id) {
        $sql = "SELECT * FROM `notes` WHERE `id` = :id";
        $stmt = DB::getConnection()->prepare($sql);
        
        // retorna um objeto da classe especificada | executar o construtor antes de instanciar os campos
        //$stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Note');
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch();
        }

        $note = new Note();
        $note->setId($result['id']);
        $note->setTitle($result['title']);
        $note->setContent($result['content']);

        $format = 'Y-m-d H:m';
        $created = date($format, strtotime($result['created_date']));
        $updated = date($format, strtotime($result['updated_date']));
        $note->setCreatedDate($created);
        $note->setUpdatedDate($updated);
    }
    echo json_encode($note);
}, ['params' => 'id']);



$router->post('/test', function() {
    echo json_encode("Hello World");
});

$router->put('/test', function() {
    echo json_encode("Hi put");
});