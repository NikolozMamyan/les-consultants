<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261116120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add persistent event registration settings with registrations closed by default.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql('CREATE TABLE event_registration_settings (event_slug VARCHAR(80) NOT NULL, registration_open TINYINT(1) NOT NULL, PRIMARY KEY(event_slug)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO event_registration_settings (event_slug, registration_open) VALUES ('after-champagne-work-2026', 0)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE event_registration_settings');
    }
}
