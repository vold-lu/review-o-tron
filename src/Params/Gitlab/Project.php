<?php

namespace App\Params\Gitlab;

class Project
{
    public function __construct(public readonly int     $id,
                                public readonly string  $name,
                                public readonly ?string $description,
                                public readonly string  $web_url,
                                public readonly ?string $avatar_url,
                                public readonly string  $git_ssh_url,
                                public readonly string  $git_http_url,
                                public readonly string  $namespace,
                                public readonly int     $visibility_level,
                                public readonly string  $path_with_namespace,
                                public readonly string  $default_branch,
                                public readonly string  $ci_config_path,
                                public readonly string  $homepage,
                                public readonly string  $url,
                                public readonly string  $ssh_url,
                                public readonly string  $http_url)
    {
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['description'],
            $data['web_url'],
            $data['avatar_url'],
            $data['git_ssh_url'],
            $data['git_http_url'],
            $data['namespace'],
            $data['visibility_level'],
            $data['path_with_namespace'],
            $data['default_branch'],
            $data['ci_config_path'],
            $data['homepage'],
            $data['url'],
            $data['ssh_url'],
            $data['http_url'],
        );
    }
}