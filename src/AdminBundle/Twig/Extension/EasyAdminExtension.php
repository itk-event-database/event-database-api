<?php

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AdminBundle\Twig\Extension;

use AdminBundle\Service\IntegrityManager;
use AdminBundle\Service\UserManager;
use AppBundle\Entity\Event;
use AppBundle\Entity\User;
use AppBundle\Security\Authorization\Voter\EditVoter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TwigExtension.
 */
class EasyAdminExtension extends \Twig_Extension
{
    /**
     * @var \AdminBundle\Twig\Extension\TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var \AppBundle\Security\Authorization\Voter\EditVoter
     */
    private $voter;

    /**
     * @var \Symfony\Component\CssSelector\XPath\TranslatorInterface
     */
    private $translator;

    /**
     * @var \AdminBundle\Service\IntegrityManager
     */
    private $integrityManager;

    /**
     * @var \AdminBundle\Service\UserManager
     */
    private $userManager;

    public function __construct(TokenStorageInterface $tokenStorage, EditVoter $voter, TranslatorInterface $translator, IntegrityManager $integrityManager, UserManager $userManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->voter = $voter;
        $this->translator = $translator;
        $this->integrityManager = $integrityManager;
        $this->userManager = $userManager;
    }

    public function getFilters()
    {
        return [
        new \Twig_SimpleFilter('exclude', [$this, 'excludeKeys'], ['is_safe' => ['all']]),
        ];
    }

    public function getFunctions()
    {
        return [
        new \Twig_Function('can_perform_action', [$this, 'canPerformAction'], ['is_safe' => ['all']]),
        new \Twig_Function('can_delete', [$this, 'canDelete'], ['is_safe' => ['all']]),
        new \Twig_Function('get_cannot_delete_info', [$this, 'getCannotDeleteInfo'], ['is_safe' => ['all']]),
        new \Twig_Function('get_entity_type', [$this, 'getEntityType'], ['is_safe' => ['all']]),
        new \Twig_Function('get_field_help', [$this, 'getFieldHelp'], ['is_safe' => ['all']]),
        ];
    }

    public function getTests()
    {
        return [
        new \Twig_Test('instanceof', function ($var, $class) {
            return $var instanceof $class;
        }),
        ];
    }

    public function excludeKeys($subject, $keys)
    {
        if (!is_array($keys)) {
            $keys = [$keys];
        }

        if (is_array($subject)) {
            foreach ($keys as $key) {
                unset($subject[$key]);
            }
        }

        if (is_object($subject)) {
            foreach ($keys as $key) {
                unset($subject->{$key});
            }
        }

        return $subject;
    }

    public function canPerformAction($action, $subject)
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return false;
        }
        if ($subject instanceof Event) {
            return $this->canPerformActionOnEvent($action, $subject, $token);
        } elseif ($subject instanceof User) {
            return $this->canPerformActionOnUser($action, $subject, $token);
        }

        return true;
    }

    public function canPerformActionOnUser($action, User $user, TokenInterface $token)
    {
        switch ($action) {
            case 'edit':
            case 'delete':
                return $this->userManager->canEdit($user, $token);
        }

        return true;
    }

    public function canDelete($entity)
    {
        return true === $this->integrityManager->canDelete($entity);
    }

    public function getCannotDeleteInfo($entity)
    {
        $info = $this->integrityManager->canDelete($entity);

        return is_array($info) ? $info : null;
    }

    public function getEntityType($entity)
    {
        return get_class($entity);
    }

    public function getFieldHelp(array $context)
    {
        if (isset($context['full_name'])) {
            // Remove numeric indexing (…[87]… -> ……);
            $key = preg_replace('/\[[0-9]+\]/', '', $context['full_name']);
            // Replace [] with .
            $key = str_replace(['[', ']'], ['.', ''], $key);
            $key = preg_replace('/^[a-z0-9]+\./', 'app.\0help.', $key);
            $translated = $this->translator->trans($key);

            if ($translated !== $key) {
                return $translated;
            }
        }

        return null;
    }

    private function canPerformActionOnEvent($action, Event $event, TokenInterface $token)
    {
        switch ($action) {
            case 'clone':
            case 'edit':
                $action = EditVoter::UPDATE;

                break;
            case 'delete':
                $action = EditVoter::REMOVE;

                break;
            case 'show':
                return true;
        }

        return VoterInterface::ACCESS_GRANTED === $this->voter->vote($token, $event, [$action]);
    }
}
