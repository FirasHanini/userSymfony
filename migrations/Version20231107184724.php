<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231107184724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reclamation ADD sender_id INT NOT NULL');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404F624B39D FOREIGN KEY (sender_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_CE606404F624B39D ON reclamation (sender_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404F624B39D');
        $this->addSql('DROP INDEX IDX_CE606404F624B39D ON reclamation');
        $this->addSql('ALTER TABLE reclamation DROP sender_id');
    }
}
