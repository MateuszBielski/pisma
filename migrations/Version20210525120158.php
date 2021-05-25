<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210525120158 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pismo ADD rodzaj_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pismo ADD CONSTRAINT FK_223710718D1E5E03 FOREIGN KEY (rodzaj_id) REFERENCES rodzaj_dokumentu (id)');
        $this->addSql('CREATE INDEX IDX_223710718D1E5E03 ON pismo (rodzaj_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pismo DROP FOREIGN KEY FK_223710718D1E5E03');
        $this->addSql('DROP INDEX IDX_223710718D1E5E03 ON pismo');
        $this->addSql('ALTER TABLE pismo DROP rodzaj_id');
    }
}
