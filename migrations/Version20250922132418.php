<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250922132418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE walk DROP INDEX UNIQ_8D917A55A7BC5DF9, ADD INDEX IDX_8D917A55A7BC5DF9 (main_photo_id)');
        $this->addSql('ALTER TABLE walk DROP FOREIGN KEY FK_8D917A55A7BC5DF9');
        $this->addSql('ALTER TABLE walk ADD CONSTRAINT FK_8D917A55A7BC5DF9 FOREIGN KEY (main_photo_id) REFERENCES main_photo (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE walk DROP INDEX IDX_8D917A55A7BC5DF9, ADD UNIQUE INDEX UNIQ_8D917A55A7BC5DF9 (main_photo_id)');
        $this->addSql('ALTER TABLE walk DROP FOREIGN KEY FK_8D917A55A7BC5DF9');
        $this->addSql('ALTER TABLE walk ADD CONSTRAINT FK_8D917A55A7BC5DF9 FOREIGN KEY (main_photo_id) REFERENCES photos (id)');
    }
}
