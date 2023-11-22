<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231107184252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur ADD reclamations_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B31853BCF7 FOREIGN KEY (reclamations_id) REFERENCES reclamation (id)');
        $this->addSql('CREATE INDEX IDX_1D1C63B31853BCF7 ON utilisateur (reclamations_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B31853BCF7');
        $this->addSql('DROP INDEX IDX_1D1C63B31853BCF7 ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP reclamations_id');
    }
}
