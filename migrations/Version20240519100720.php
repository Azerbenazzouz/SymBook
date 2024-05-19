<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240519100720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE etat_order (id INT AUTO_INCREMENT NOT NULL, etat VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE payement_type (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE `order` ADD etat_payement TINYINT(1) NOT NULL, ADD etat_id INT NOT NULL, ADD payement_type_id INT NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398D5E86FF FOREIGN KEY (etat_id) REFERENCES etat_order (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398BEC4000E FOREIGN KEY (payement_type_id) REFERENCES payement_type (id)');
        $this->addSql('CREATE INDEX IDX_F5299398D5E86FF ON `order` (etat_id)');
        $this->addSql('CREATE INDEX IDX_F5299398BEC4000E ON `order` (payement_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE etat_order');
        $this->addSql('DROP TABLE payement_type');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398D5E86FF');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398BEC4000E');
        $this->addSql('DROP INDEX IDX_F5299398D5E86FF ON `order`');
        $this->addSql('DROP INDEX IDX_F5299398BEC4000E ON `order`');
        $this->addSql('ALTER TABLE `order` DROP etat_payement, DROP etat_id, DROP payement_type_id');
    }
}
