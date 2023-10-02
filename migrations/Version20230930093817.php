<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230930093817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE repeated_assignment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE repeated_assignment (id INT NOT NULL, person_id INT NOT NULL, seat_id INT NOT NULL, day_of_week INT NOT NULL, from_time TIME(0) WITHOUT TIME ZONE NOT NULL, to_time TIME(0) WITHOUT TIME ZONE NOT NULL, until_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FA68C0BC217BBB47 ON repeated_assignment (person_id)');
        $this->addSql('CREATE INDEX IDX_FA68C0BCC1DAFE35 ON repeated_assignment (seat_id)');
        $this->addSql('ALTER TABLE repeated_assignment ADD CONSTRAINT FK_FA68C0BC217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE repeated_assignment ADD CONSTRAINT FK_FA68C0BCC1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE repeated_assignment_id_seq CASCADE');
        $this->addSql('ALTER TABLE repeated_assignment DROP CONSTRAINT FK_FA68C0BC217BBB47');
        $this->addSql('ALTER TABLE repeated_assignment DROP CONSTRAINT FK_FA68C0BCC1DAFE35');
        $this->addSql('DROP TABLE repeated_assignment');
    }
}
