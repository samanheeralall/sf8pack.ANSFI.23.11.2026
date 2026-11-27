<?php

namespace App\Security\Voter;

use App\Entity\Book;
use App\Security\Permission\BookPermission;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class BookVoter extends Voter
{
    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [BookPermission::EDIT_DETAILS, BookPermission::CHANGE_AVAILABILITY])
            && $subject instanceof Book;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            $vote?->addReason('The user must be logged in to access this resource.');

            return false;
        }


        return match ($attribute) {
            BookPermission::EDIT_DETAILS =>
                $this->accessDecisionManager->decide($token, ['ROLE_LIBRARIAN']) || $subject->getAddedBy() === $user,
            BookPermission::CHANGE_AVAILABILITY =>
            $this->accessDecisionManager->decide($token, ['ROLE_LIBRARIAN']),
            default => false,
        };
    }
}
