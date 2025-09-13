<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250815130359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE channel (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE channel_user (channel_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_11C7753772F5A1AA (channel_id), INDEX IDX_11C77537A76ED395 (user_id), PRIMARY KEY(channel_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat (id INT AUTO_INCREMENT NOT NULL, walk_id INT NOT NULL, UNIQUE INDEX UNIQ_659DF2AA5EEE1B48 (walk_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_message (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, chat_id INT NOT NULL, content LONGTEXT NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_FAB3FC16F624B39D (sender_id), INDEX IDX_FAB3FC161A9A7125 (chat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, name VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE main_photo (id INT AUTO_INCREMENT NOT NULL, file_path VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, user_id INT NOT NULL, is_read TINYINT(1) NOT NULL, content VARCHAR(255) NOT NULL, INDEX IDX_BF5476CA72F5A1AA (channel_id), INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE park (id INT AUTO_INCREMENT NOT NULL, location_id INT NOT NULL, description VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_C4077D3364D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refresh_token (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(255) NOT NULL, username VARCHAR(180) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_C74F2195C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(100) NOT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, is_verified TINYINT(1) DEFAULT 0 NOT NULL, cgv_accepted TINYINT(1) NOT NULL, confirmation_requested_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE walk (id INT AUTO_INCREMENT NOT NULL, creator_id INT NOT NULL, location_id INT DEFAULT NULL, main_photo_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date DATETIME NOT NULL, max_participants INT NOT NULL, is_custom_location TINYINT(1) NOT NULL, INDEX IDX_8D917A5561220EA6 (creator_id), INDEX IDX_8D917A5564D218E (location_id), INDEX IDX_8D917A55A7BC5DF9 (main_photo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE walk_participants (walk_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_FA8853E95EEE1B48 (walk_id), INDEX IDX_FA8853E9A76ED395 (user_id), PRIMARY KEY(walk_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE walk_alert_request (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, walk_id INT DEFAULT NULL, requested_at DATETIME NOT NULL, notified TINYINT(1) NOT NULL, INDEX IDX_629861CAA76ED395 (user_id), INDEX IDX_629861CA5EEE1B48 (walk_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE channel_user ADD CONSTRAINT FK_11C7753772F5A1AA FOREIGN KEY (channel_id) REFERENCES channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE channel_user ADD CONSTRAINT FK_11C77537A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat ADD CONSTRAINT FK_659DF2AA5EEE1B48 FOREIGN KEY (walk_id) REFERENCES walk (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16F624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC161A9A7125 FOREIGN KEY (chat_id) REFERENCES chat (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA72F5A1AA FOREIGN KEY (channel_id) REFERENCES channel (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE park ADD CONSTRAINT FK_C4077D3364D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE walk ADD CONSTRAINT FK_8D917A5561220EA6 FOREIGN KEY (creator_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE walk ADD CONSTRAINT FK_8D917A5564D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE walk ADD CONSTRAINT FK_8D917A55A7BC5DF9 FOREIGN KEY (main_photo_id) REFERENCES main_photo (id)');
        $this->addSql('ALTER TABLE walk_participants ADD CONSTRAINT FK_FA8853E95EEE1B48 FOREIGN KEY (walk_id) REFERENCES walk (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE walk_participants ADD CONSTRAINT FK_FA8853E9A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE walk_alert_request ADD CONSTRAINT FK_629861CAA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE walk_alert_request ADD CONSTRAINT FK_629861CA5EEE1B48 FOREIGN KEY (walk_id) REFERENCES walk (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE channel_user DROP FOREIGN KEY FK_11C7753772F5A1AA');
        $this->addSql('ALTER TABLE channel_user DROP FOREIGN KEY FK_11C77537A76ED395');
        $this->addSql('ALTER TABLE chat DROP FOREIGN KEY FK_659DF2AA5EEE1B48');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16F624B39D');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC161A9A7125');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA72F5A1AA');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE park DROP FOREIGN KEY FK_C4077D3364D218E');
        $this->addSql('ALTER TABLE walk DROP FOREIGN KEY FK_8D917A5561220EA6');
        $this->addSql('ALTER TABLE walk DROP FOREIGN KEY FK_8D917A5564D218E');
        $this->addSql('ALTER TABLE walk DROP FOREIGN KEY FK_8D917A55A7BC5DF9');
        $this->addSql('ALTER TABLE walk_participants DROP FOREIGN KEY FK_FA8853E95EEE1B48');
        $this->addSql('ALTER TABLE walk_participants DROP FOREIGN KEY FK_FA8853E9A76ED395');
        $this->addSql('ALTER TABLE walk_alert_request DROP FOREIGN KEY FK_629861CAA76ED395');
        $this->addSql('ALTER TABLE walk_alert_request DROP FOREIGN KEY FK_629861CA5EEE1B48');
        $this->addSql('DROP TABLE channel');
        $this->addSql('DROP TABLE channel_user');
        $this->addSql('DROP TABLE chat');
        $this->addSql('DROP TABLE chat_message');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE main_photo');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE park');
        $this->addSql('DROP TABLE refresh_token');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE walk');
        $this->addSql('DROP TABLE walk_participants');
        $this->addSql('DROP TABLE walk_alert_request');
    }
}
