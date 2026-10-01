<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261001103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the configurable catalogue and seed the current 38-page training catalogue.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql("CREATE TABLE catalog (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("CREATE TABLE catalog_page (id INT AUTO_INCREMENT NOT NULL, catalog_id INT NOT NULL, position_index INT NOT NULL, title VARCHAR(180) NOT NULL, image_path VARCHAR(255) DEFAULT NULL, pdf_path VARCHAR(255) DEFAULT NULL, pdf_page INT DEFAULT NULL, link_url VARCHAR(2048) DEFAULT NULL, open_link_in_new_tab TINYINT(1) NOT NULL, enabled TINYINT(1) NOT NULL, updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_61BF0656CC3C66FC (catalog_id), INDEX idx_catalog_page_position (catalog_id, position_index), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE catalog_page ADD CONSTRAINT FK_CATALOG_PAGE_CATALOG FOREIGN KEY (catalog_id) REFERENCES catalog (id) ON DELETE CASCADE');
        $this->addSql("INSERT INTO catalog (id, title, enabled, updated_at) VALUES (1, 'Catalogue des formations 2026', 1, CURRENT_TIMESTAMP)");

        $this->insertPage(1, 'Couverture', '/catalogue/default/pages/00-cover.webp');
        $this->insertPage(2, 'Notre approche', '/catalogue/default/pages/01-introduction.webp');

        $titles = [
            'KYC, Beneficial Ownership & Control',
            'Digital Regulation Literacy (DORA / CSSF)',
            'FATCA / CRS for the 1st and 2nd Lines of Defence',
            'Understanding and Mastering GDPR Rules in Luxembourg',
            'AML/CFT for Payment Institutions & FinTechs',
            'Cyber Risk Management in Luxembourg',
            'AML Tax Fundamentals and Customer Risk Assessment in Luxembourg',
            'Master the Fundamentals of AML/CFT in Luxembourg',
            'AML/CFT for Transfer Agents, Fund Managers and Distributors',
            'International Sanctions',
            'DORA & CSSF Regulatory Framework',
            'AIFMD 2.0: Navigating Luxembourg’s New Regulatory Landscape',
            'AI Literacy: MS Copilot and ChatGPT-4',
            'Know Your Asset (KYA)',
            'EU AI Act Compliance and Governance',
            'Whistleblower Protection',
            'Anti-Corruption and Bribery (ABC) for Financial Sector Professionals in Luxembourg',
            'Market Abuse and Personal Transactions (MAR)',
            'AML/CFT applied to Luxembourg Accountants and Fiduciaries',
            'AML/CFT applied to Luxembourg Real Estate',
            'AML/CFT applied to Luxembourg Insurance Sector',
            'Introduction to Operational Risk',
            'Introduction to Trusts for Insurance Companies',
            'Introduction to MiCA (Markets in Crypto-Assets)',
            'Introduction to DAC6',
            'Introduction to the Trust: A Specific Legal Structure',
            'Mastering the Fundamental Rules of AML/CFT in Luxembourg',
            'EMIR – European Market Infrastructure Regulation',
            'Training on MiFID II in Luxembourg – Part 1',
            'Training on MiFID II in Luxembourg – Part 2',
            'Introduction to FATCA / CRS',
            'Introduction to the GDPR in Luxembourg: Master the Fundamentals of Data Protection',
            'Introduction to Cyber Risk Management',
            'Identification of Beneficial Owners in Luxembourg',
        ];

        foreach ($titles as $index => $title) {
            $number = $index + 1;
            $file = str_pad((string) $number, 2, '0', STR_PAD_LEFT);
            $this->insertPage($number + 2, $title, '/catalogue/default/pages/'.$file.'.webp', '/catalogue/default/pdf/'.$file.'.pdf');
        }

        $this->insertPage(37, 'Programme sur mesure', '/catalogue/default/pages/36-contact.webp', null, 'mailto:pruffin@les-consultants.lu');
        $this->insertPage(38, 'Merci', '/catalogue/default/pages/37-back.webp', null, 'https://les-consultants.lu');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof AbstractMySQLPlatform, 'Migration can only be executed safely on MySQL.');

        $this->addSql('ALTER TABLE catalog_page DROP FOREIGN KEY FK_CATALOG_PAGE_CATALOG');
        $this->addSql('DROP TABLE catalog_page');
        $this->addSql('DROP TABLE catalog');
    }

    private function insertPage(int $position, string $title, string $imagePath, ?string $pdfPath = null, ?string $linkUrl = null): void
    {
        $this->addSql(
            'INSERT INTO catalog_page (catalog_id, position_index, title, image_path, pdf_path, pdf_page, link_url, open_link_in_new_tab, enabled, updated_at) VALUES (1, ?, ?, ?, ?, NULL, ?, ?, 1, CURRENT_TIMESTAMP)',
            [$position, $title, $imagePath, $pdfPath, $linkUrl, null !== $linkUrl && str_starts_with($linkUrl, 'http') ? 1 : 0],
        );
    }
}
