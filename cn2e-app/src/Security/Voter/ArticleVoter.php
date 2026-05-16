<?php

namespace App\Security\Voter;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter
{
    public const VIEW = 'ARTICLE_VIEW';
    public const CREATE = 'ARTICLE_CREATE';
    public const EDIT = 'ARTICLE_EDIT';
    public const DELETE = 'ARTICLE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::VIEW,
            self::CREATE,
            self::EDIT,
            self::DELETE
        ]);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // SUPER_ADMIN override total
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        return match ($attribute) {
            self::CREATE => $this->canCreate($user),
            self::EDIT => $this->canEdit($user, $subject),
            self::DELETE => $this->canDelete($user, $subject),
            self::VIEW => $this->canView($user, $subject),
            default => false,
        };
    }

    private function canCreate(User $user): bool
    {
        return in_array('ROLE_CN2E_ADMIN', $user->getRoles(), true);
    }

    private function canEdit(User $user, Article $article): bool
    {
        return in_array('ROLE_CN2E_ADMIN', $user->getRoles(), true)
            && $article->getAuthor() === $user;
    }

    private function canDelete(User $user, Article $article): bool
    {
        return in_array('ROLE_CN2E_ADMIN', $user->getRoles(), true)
            && $article->getAuthor() === $user;
    }

    private function canView(User $user, ?Article $article = null): bool
    {
        // CN2E ADMIN voit uniquement ses propres articles
        return in_array('ROLE_CN2E_ADMIN', $user->getRoles(), true)
            && $article->getAuthor() === $user;
    }
}