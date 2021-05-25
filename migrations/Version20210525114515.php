<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210525114515 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE rodzaj_dokumentu (id INT AUTO_INCREMENT NOT NULL, nazwa VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sprawa (id INT AUTO_INCREMENT NOT NULL, nazwa VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sprawa_pismo (sprawa_id INT NOT NULL, pismo_id INT NOT NULL, INDEX IDX_FD1E854C6A40D748 (sprawa_id), INDEX IDX_FD1E854CC10D702B (pismo_id), PRIMARY KEY(sprawa_id, pismo_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sprawa_pismo ADD CONSTRAINT FK_FD1E854C6A40D748 FOREIGN KEY (sprawa_id) REFERENCES sprawa (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sprawa_pismo ADD CONSTRAINT FK_FD1E854CC10D702B FOREIGN KEY (pismo_id) REFERENCES pismo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pismo ADD nadawca_id INT DEFAULT NULL, ADD odbiorca_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pismo ADD CONSTRAINT FK_22371071CCF39CE2 FOREIGN KEY (nadawca_id) REFERENCES kontrahent (id)');
        $this->addSql('ALTER TABLE pismo ADD CONSTRAINT FK_22371071328A74B5 FOREIGN KEY (odbiorca_id) REFERENCES kontrahent (id)');
        $this->addSql('CREATE INDEX IDX_22371071CCF39CE2 ON pismo (nadawca_id)');
        $this->addSql('CREATE INDEX IDX_22371071328A74B5 ON pismo (odbiorca_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sprawa_pismo DROP FOREIGN KEY FK_FD1E854C6A40D748');
        $this->addSql('DROP TABLE rodzaj_dokumentu');
        $this->addSql('DROP TABLE sprawa');
        $this->addSql('DROP TABLE sprawa_pismo');
        $this->addSql('ALTER TABLE pismo DROP FOREIGN KEY FK_22371071CCF39CE2');
        $this->addSql('ALTER TABLE pismo DROP FOREIGN KEY FK_22371071328A74B5');
        $this->addSql('DROP INDEX IDX_22371071CCF39CE2 ON pismo');
        $this->addSql('DROP INDEX IDX_22371071328A74B5 ON pismo');
        $this->addSql('ALTER TABLE pismo DROP nadawca_id, DROP odbiorca_id');
    }
}
