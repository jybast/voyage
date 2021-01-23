<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201108142508 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6660BB6FE6');
        $this->addSql('CREATE FULLTEXT INDEX IDX_CD8737FAFF7747B42C5F69E389C2003F ON article (titre, soustitre, contenu)');
        $this->addSql('DROP INDEX idx_23a0e6660bb6fe6 ON article');
        $this->addSql('CREATE INDEX IDX_CD8737FA60BB6FE6 ON article (auteur_id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6660BB6FE6 FOREIGN KEY (auteur_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_CD8737FAFF7747B42C5F69E389C2003F ON Article');
        $this->addSql('ALTER TABLE Article DROP FOREIGN KEY FK_CD8737FA60BB6FE6');
        $this->addSql('DROP INDEX idx_cd8737fa60bb6fe6 ON Article');
        $this->addSql('CREATE INDEX IDX_23A0E6660BB6FE6 ON Article (auteur_id)');
        $this->addSql('ALTER TABLE Article ADD CONSTRAINT FK_CD8737FA60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES user (id)');
    }
}
