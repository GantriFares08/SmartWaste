<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260516142418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collecte ADD CONSTRAINT FK_55AE4A3D1614F182 FOREIGN KEY (collecteur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE poubelle ADD CONSTRAINT FK_B5344EA3BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_poubelle (id)');
        $this->addSql('ALTER TABLE rapport ADD CONSTRAINT FK_BE34A09C710A9AC6 FOREIGN KEY (collecte_id) REFERENCES collecte (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B55114FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B55114F231B082 FOREIGN KEY (poubelle_id) REFERENCES poubelle (id)');
        $this->addSql('ALTER TABLE signalement ADD CONSTRAINT FK_F4B55114BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_signalement (id)');
        $this->addSql('ALTER TABLE utilisateur ADD telephone VARCHAR(255) DEFAULT NULL, ADD ville VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3D1614F182');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAFB88E14F');
        $this->addSql('ALTER TABLE poubelle DROP FOREIGN KEY FK_B5344EA3BCF5E72D');
        $this->addSql('ALTER TABLE rapport DROP FOREIGN KEY FK_BE34A09C710A9AC6');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114FB88E14F');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114F231B082');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114BCF5E72D');
        $this->addSql('ALTER TABLE utilisateur DROP telephone, DROP ville');
    }
}
