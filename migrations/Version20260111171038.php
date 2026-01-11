<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260111171038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create MediaObject entity and add relations with Actor and Movie';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Create media_object table
        $this->addSql('CREATE TABLE media_object (id INT AUTO_INCREMENT NOT NULL, content_url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Modify actor table: change photo from VARCHAR to FK
        $this->addSql('ALTER TABLE actor DROP photo');
        $this->addSql('ALTER TABLE actor ADD photo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE actor ADD CONSTRAINT FK_447556F97E9E4C8C FOREIGN KEY (photo_id) REFERENCES media_object (id)');
        $this->addSql('CREATE INDEX IDX_447556F97E9E4C8C ON actor (photo_id)');
        
        // Modify movie table: change image from VARCHAR to FK
        $this->addSql('ALTER TABLE movie DROP image');
        $this->addSql('ALTER TABLE movie ADD image_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE movie ADD CONSTRAINT FK_1D5EF26F3DA5256D FOREIGN KEY (image_id) REFERENCES media_object (id)');
        $this->addSql('CREATE INDEX IDX_1D5EF26F3DA5256D ON movie (image_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actor DROP FOREIGN KEY FK_447556F97E9E4C8C');
        $this->addSql('ALTER TABLE movie DROP FOREIGN KEY FK_1D5EF26F3DA5256D');
        $this->addSql('DROP INDEX IDX_447556F97E9E4C8C ON actor');
        $this->addSql('DROP INDEX IDX_1D5EF26F3DA5256D ON movie');
        $this->addSql('ALTER TABLE actor DROP photo_id');
        $this->addSql('ALTER TABLE actor ADD photo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE movie DROP image_id');
        $this->addSql('ALTER TABLE movie ADD image VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP TABLE media_object');
    }
}
