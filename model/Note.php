<?php

class Note {
    function __construct() {}

    function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    function getTitle() {
        return $this->title;
    }

    function setContent($content) {
        $this->content = $content;
    }

    function getContent() {
        return $this->content;
    }

    function setCreatedDate($createdDate) {
        $this->createdDate = $createdDate;
    }

    function getCreatedDate() {
        return $this->createdDate;
    }

    function setUpdatedDate($updatedDate) {
        $this->updatedDate = $updatedDate;
    }

    function getUpdatedDate() {
        return $this->updatedDate;
    }
}