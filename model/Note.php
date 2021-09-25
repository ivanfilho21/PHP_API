<?php

class Note {
    private $id;
    private $title;
    private $content;
    private $createdDate;
    private $updatedDate;


    function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $id;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    function getTitle() {
        return $title;
    }

    function setContent($content) {
        $this->content = $content;
    }

    function getContent() {
        return $content;
    }

    function setCreatedDate($createdDate) {
        $this->createdDate = $createdDate;
    }

    function getCreatedDate() {
        return $createdDate;
    }

    function setUpdatedDate($updatedDate) {
        $this->updatedDate = $updatedDate;
    }

    function getUpdatedDate() {
        return $updatedDate;
    }
}