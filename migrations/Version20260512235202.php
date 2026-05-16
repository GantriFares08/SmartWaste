<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512235202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie_poubelle (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE categorie_signalement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE collecte (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(255) NOT NULL, camion VARCHAR(255) NOT NULL, statut VARCHAR(50) NOT NULL, date_collecte DATETIME NOT NULL, collecteur_id INT NOT NULL, INDEX IDX_55AE4A3D1614F182 (collecteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE historique_statut (id INT AUTO_INCREMENT NOT NULL, entite_concernee VARCHAR(255) NOT NULL, entite_id INT NOT NULL, nouveau_statut VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, lue TINYINT NOT NULL, created_at DATETIME NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_BF5476CAFB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE parametre (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, valeur LONGTEXT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE poubelle (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, categorie_id INT NOT NULL, INDEX IDX_B5344EA3BCF5E72D (categorie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE rapport (id INT AUTO_INCREMENT NOT NULL, description LONGTEXT NOT NULL, photo VARCHAR(255) DEFAULT NULL, collecte_id INT NOT NULL, UNIQUE INDEX UNIQ_BE34A09C710A9AC6 (collecte_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE signalement (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, photo VARCHAR(255) DEFAULT NULL, statut VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, utilisateur_id INT NOT NULL, poubelle_id INT DEFAULT NULL, categorie_id INT NOT NULL, INDEX IDX_F4B55114FB88E14F (utilisateur_id), INDEX IDX_F4B55114F231B082 (poubelle_id), INDEX IDX_F4B55114BCF5E72D (categorie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
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
        $this->addSql('ALTER TABLE collecte DROP FOREIGN KEY FK_55AE4A3D1614F182');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAFB88E14F');
        $this->addSql('ALTER TABLE poubelle DROP FOREIGN KEY FK_B5344EA3BCF5E72D');
        $this->addSql('ALTER TABLE rapport DROP FOREIGN KEY FK_BE34A09C710A9AC6');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114FB88E14F');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114F231B082');
        $this->addSql('ALTER TABLE signalement DROP FOREIGN KEY FK_F4B55114BCF5E72D');
        $this->addSql('DROP TABLE categorie_poubelle');
        $this->addSql('DROP TABLE categorie_signalement');
        $this->addSql('DROP TABLE collecte');
        $this->addSql('DROP TABLE historique_statut');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE parametre');
        $this->addSql('DROP TABLE poubelle');
        $this->addSql('DROP TABLE rapport');
        $this->addSql('DROP TABLE signalement');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
