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
final class Version20180826130109 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO itkdev_setting (name, type, form_type, description, value_text) VALUES (:name, :type, :form_type, :description, :value)', [
            'name' => 'app_terms_content',
            'type' => 'text',
            'form_type' => 'ckeditor',
            'description' => 'The terms and conditions for using the app',
            'value' => '
<h2>Terms and conditions</h2>
',
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM itkdev_setting WHERE name = 'app_terms_content'");
    }
}
