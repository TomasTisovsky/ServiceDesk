<?php


namespace App\Services;

use App\Repositories\CommentRepository;
use InvalidArgumentException;

class CommentService
{
    public function __construct(private CommentRepository $comments)
    {
    }

    public function add(
        int    $ticketId,
        int    $userId,
        string $role,
        string $content
    ): bool
    {
        $content = trim($content);

        if ($content === '') {
            throw new InvalidArgumentException('Vyplň text komentára.');
        }

        if (strlen($content) > 4000) {
            throw new InvalidArgumentException(
                'Komentár môže mať najviac 4000 bajtov.'
            );
        }

        return $this->comments->add(
            $ticketId,
            $userId,
            $role,
            $content
        );
    }
}