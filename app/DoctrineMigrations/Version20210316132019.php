<?php

declare(strict_types=1);

/*
 * This file is part of Eventbase API.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210316132019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('UPDATE user SET roles = REPLACE(roles, \'s:17:"ROLE_EVENT_EDITOR";\', \'s:29:"ROLE_FULL_ACCESS_EVENT_EDITOR";\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('UPDATE user SET roles = REPLACE(roles, \'s:29:"ROLE_FULL_ACCESS_EVENT_EDITOR";\', \'s:17:"ROLE_EVENT_EDITOR";\')');
    }
}
