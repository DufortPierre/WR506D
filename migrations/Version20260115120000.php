<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260115120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove unused fields relationMovies and namecategory from category table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP relation_movies');
        $this->addSql('ALTER TABLE category DROP namecategory');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD relation_movies VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE category ADD namecategory VARCHAR(255) NOT NULL');
    }
}
