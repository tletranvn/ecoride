<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250603085745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, trajet_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_AB55E24FA76ED395 (user_id), INDEX IDX_AB55E24FD12A823 (trajet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation ADD CONSTRAINT FK_AB55E24FD12A823 FOREIGN KEY (trajet_id) REFERENCES trajet (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24FA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24FD12A823
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE participation
        SQL);
    }
}
