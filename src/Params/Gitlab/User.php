<?php

namespace App\Params\Gitlab;

class User
{
    public function __construct(public readonly int    $id,
                                public readonly string $name,
                                public readonly string $username,
                                public readonly string $avatar_url,
                                public readonly string $email)
    {
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['username'],
            $data['avatar_url'],
            $data['email']
        );
    }
}