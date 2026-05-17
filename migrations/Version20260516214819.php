<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260516214819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, contenu LONGTEXT NOT NULL, created_at DATETIME NOT NULL, lu TINYINT NOT NULL, expediteur_id INT NOT NULL, destinataire_id INT DEFAULT NULL, INDEX IDX_B6BD307F10335F61 (expediteur_id), INDEX IDX_B6BD307FA4F84F6E (destinataire_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F10335F61 FOREIGN KEY (expediteur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA4F84F6E FOREIGN KEY (destinataire_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE collecte ADD CONSTRAINT FK_55AE4A3D1614F182 FOREIGN KEY (collecteur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE poubelle ADD CONSTRAINT FK_B5344EA3BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_poubelle (id)');
        $this->addSql('ALTER TABLE rapport ADD CONSTRAINT FK_BE34A09C710A9AC6 FOREIGN KEY (collecte_id) REFERENCES collecte (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B55114FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B55114F231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B55114BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_signalement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F10335F61');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA4F84F6E');
        $this->addSql('DROP TABLE message');
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3D1614F182');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAFB88E14F');
        $this->addSql('ALTER TABLE poubelle DROP FOREIGN KEY FK_B5344EA3BCF5E72D');
        $this->addSql('ALTER TABLE rapport DROP FOREIGN KEY FK_BE34A09C710A9AC6');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114FB88E14F');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114F231B082');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114BCF5E72D');
    }
}
