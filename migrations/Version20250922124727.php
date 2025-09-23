<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922124727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE photos (id INT AUTO_INCREMENT NOT NULL, file_name VARCHAR(255) NOT NULL, entity_type VARCHAR(50) NOT NULL, entity_id INT NOT NULL, photo_type VARCHAR(50) NOT NULL, uploaded_at DATETIME NOT NULL, original_name VARCHAR(255) DEFAULT NULL, file_size INT DEFAULT NULL, mime_type VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE location CHANGE city city VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE walk DROP INDEX IDX_8D917A55A7BC5DF9, ADD UNIQUE INDEX UNIQ_8D917A55A7BC5DF9 (main_photo_id)');
        $this->addSql('ALTER TABLE walk DROP FOREIGN KEY FK_8D917A55A7BC5DF9');
        $this->addSql('ALTER TABLE walk ADD CONSTRAINT FK_8D917A55A7BC5DF9 FOREIGN KEY (main_photo_id) REFERENCES photos (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE walk DROP FOREIGN KEY FK_8D917A55A7BC5DF9');
        $this->addSql('DROP TABLE photos');
        $this->addSql('ALTER TABLE location CHANGE city city VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE walk DROP INDEX UNIQ_8D917A55A7BC5DF9, ADD INDEX IDX_8D917A55A7BC5DF9 (main_photo_id)');
        $this->addSql('ALTER TABLE walk DROP FOREIGN KEY FK_8D917A55A7BC5DF9');
        $this->addSql('ALTER TABLE walk ADD CONSTRAINT FK_8D917A55A7BC5DF9 FOREIGN KEY (main_photo_id) REFERENCES main_photo (id)');
    }
}
