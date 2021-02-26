<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Security\Domain\Authentication\Model;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @package Security\Authentication
 */
class AuthenticatedToken implements TokenInterface
{
    /**
     * @param string|\Stringable|UserInterface
     */
    private $user;
    /**
     * @param bool
     */
    private $isAuthenticated = false;
    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @var Role[]
     */
    private $roles = [];
    /**
     * @var array
     */
    private $roleNames = [];

    /**
     * Token constructor.
     *
     * @param $roles Role[]|string[]
     */
    public function __construct($roles)
    {
        foreach ($roles as $role) {
            if (\is_string($role)) {
                $role = new Role($role, false);
            } elseif (!$role instanceof Role) {
                throw new \InvalidArgumentException(
                    sprintf('$roles must be an array of strings, but got "%s".', \gettype($role))
                );
            }

            $this->roles[] = $role;
            $this->roleNames[] = (string) $role;
        }
    }

    public function __serialize(): array
    {
        return [$this->user, $this->isAuthenticated, $this->roles, $this->attributes, $this->roleNames];
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return $this->__serialize();
    }

    /**
     * @param array<mixed> $data
     */
    public function __unserialize(array $data): void
    {
        [$this->user, $this->isAuthenticated, $this->roles, $this->attributes] = $data;

        // migration path to 4.3+
        if (null === $this->roleNames = $data[4] ?? null) {
            $this->roleNames = [];
            foreach ($this->roles as $role) {
                $this->roleNames[] = (string) $role;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $this->__unserialize(\is_array($serialized) ? $serialized : unserialize($serialized));
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $class = static::class;
        $class = substr($class, strrpos($class, '\\') + 1);

        $roles = [];
        foreach ($this->roles as $role) {
            $roles[] = $role->getRole();
        }

        return sprintf(
            '%s(user="%s", authenticated=%s, roles="%s")',
            $class,
            $this->getUsername(),
            json_encode($this->isAuthenticated),
            implode(', ', $roles)
        );
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return [new Role('ROLE_ADMIN'), new Role('ROLE_LOLO')];
    }

    /**
     * @inheritDoc
     */
    public function getCredentials()
    {
        return ['login' => 'blablabla'];
    }

    /**
     * @inheritDoc
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @inheritDoc
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return 'my username';
    }

    /**
     * @inheritDoc
     */
    public function isAuthenticated()
    {
        return $this->isAuthenticated;
    }

    /**
     * @inheritDoc
     */
    public function setAuthenticated($isAuthenticated)
    {
        $this->isAuthenticated = $isAuthenticated;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        if ($this->getUser() instanceof UserInterface) {
            $this->getUser()->eraseCredentials();
        }
    }

    /**
     * @inheritDoc
     */
    public function getAttributes()
    {
        // TODO: Implement getAttributes() method.
    }

    /**
     * @inheritDoc
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @inheritDoc
     */
    public function hasAttribute($name)
    {
        return \array_key_exists($name, $this->attributes);
    }

    /**
     * @inheritDoc
     */
    public function getAttribute($name)
    {
        if (!\array_key_exists($name, $this->attributes)) {
            throw new \InvalidArgumentException(sprintf('This token has no "%s" attribute.', $name));
        }

        return $this->attributes[$name];
    }

    /**
     * @inheritDoc
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function getRoleNames(): array
    {
        return $this->roleNames;
    }
}
