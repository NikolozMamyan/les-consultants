<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260923120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Persist mission and consultant profile submissions.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql("CREATE TABLE lead_submission (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(16) NOT NULL, subject VARCHAR(160) NOT NULL, contact_name VARCHAR(160) NOT NULL, organization VARCHAR(160) DEFAULT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(35) DEFAULT NULL, expertise VARCHAR(160) DEFAULT NULL, details LONGTEXT NOT NULL, start_date DATE DEFAULT NULL COMMENT '(DC2Type:date_immutable)', duration VARCHAR(80) DEFAULT NULL, status VARCHAR(24) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX idx_submission_type_created_at (type, created_at), INDEX idx_submission_status (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql('DROP TABLE lead_submission');
    }
}
