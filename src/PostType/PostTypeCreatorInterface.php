<?php
namespace ProfileCreator\PostType;

interface PostTypeCreatorInterface {
    public function register_post_types(): void;
}