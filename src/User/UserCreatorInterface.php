<?php
namespace ProfileCreator\User;

interface UserCreatorInterface {
    public function create_user( string $name, string $email, string $bio ): int|\WP_Error;
}