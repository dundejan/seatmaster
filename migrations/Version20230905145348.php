<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230905145348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE assignment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE office_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE person_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE seat_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE assignment (id INT NOT NULL, person_id INT NOT NULL, seat_id INT NOT NULL, from_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, to_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_30C544BA217BBB47 ON assignment (person_id)');
        $this->addSql('CREATE INDEX IDX_30C544BAC1DAFE35 ON assignment (seat_id)');
        $this->addSql('CREATE TABLE office (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE person (id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, id_external INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE seat (id INT NOT NULL, office_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3D5C3666FFA0C224 ON seat (office_id)');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BA217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BAC1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE seat ADD CONSTRAINT FK_3D5C3666FFA0C224 FOREIGN KEY (office_id) REFERENCES office (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE assignment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE office_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE person_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE seat_id_seq CASCADE');
        $this->addSql('ALTER TABLE assignment DROP CONSTRAINT FK_30C544BA217BBB47');
        $this->addSql('ALTER TABLE assignment DROP CONSTRAINT FK_30C544BAC1DAFE35');
        $this->addSql('ALTER TABLE seat DROP CONSTRAINT FK_3D5C3666FFA0C224');
        $this->addSql('DROP TABLE assignment');
        $this->addSql('DROP TABLE office');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE seat');
    }
}
