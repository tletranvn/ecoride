<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250602070434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE trajet ADD chauffeur_id INT NOT NULL, ADD vehicule_id INT NOT NULL, ADD ville_depart VARCHAR(255) NOT NULL, ADD ville_arrivee VARCHAR(255) NOT NULL, ADD date_depart DATETIME NOT NULL, ADD places_total INT NOT NULL, ADD places_restantes INT NOT NULL, ADD prix NUMERIC(10, 2) NOT NULL, ADD is_eco_certifie TINYINT(1) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trajet ADD CONSTRAINT FK_2B5BA98C85C0B3BE FOREIGN KEY (chauffeur_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trajet ADD CONSTRAINT FK_2B5BA98C4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2B5BA98C85C0B3BE ON trajet (chauffeur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2B5BA98C4A4A3511 ON trajet (vehicule_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE trajet DROP FOREIGN KEY FK_2B5BA98C85C0B3BE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trajet DROP FOREIGN KEY FK_2B5BA98C4A4A3511
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_2B5BA98C85C0B3BE ON trajet
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_2B5BA98C4A4A3511 ON trajet
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trajet DROP chauffeur_id, DROP vehicule_id, DROP ville_depart, DROP ville_arrivee, DROP date_depart, DROP places_total, DROP places_restantes, DROP prix, DROP is_eco_certifie, DROP created_at, DROP updated_at
        SQL);
    }
}
