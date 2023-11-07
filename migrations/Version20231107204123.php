<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231107204123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE api_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE assignment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE office_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE person_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE repeated_assignment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE seat_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE api_token (id INT NOT NULL, owned_by_id INT NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, token VARCHAR(68) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7BA2F5EB5E70BCD7 ON api_token (owned_by_id)');
        $this->addSql('COMMENT ON COLUMN api_token.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE assignment (id INT NOT NULL, person_id INT NOT NULL, seat_id INT NOT NULL, from_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, to_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_30C544BA217BBB47 ON assignment (person_id)');
        $this->addSql('CREATE INDEX IDX_30C544BAC1DAFE35 ON assignment (seat_id)');
        $this->addSql('CREATE TABLE office (id INT NOT NULL, name VARCHAR(255) NOT NULL, width INT NOT NULL, height INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE person (id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, id_external VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, job_title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE repeated_assignment (id INT NOT NULL, person_id INT NOT NULL, seat_id INT NOT NULL, day_of_week INT NOT NULL, from_time TIME(0) WITHOUT TIME ZONE NOT NULL, to_time TIME(0) WITHOUT TIME ZONE NOT NULL, until_date DATE DEFAULT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FA68C0BC217BBB47 ON repeated_assignment (person_id)');
        $this->addSql('CREATE INDEX IDX_FA68C0BCC1DAFE35 ON repeated_assignment (seat_id)');
        $this->addSql('CREATE TABLE seat (id INT NOT NULL, office_id INT NOT NULL, coord_x INT DEFAULT NULL, coord_y INT DEFAULT NULL, rotation INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3D5C3666FFA0C224 ON seat (office_id)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EB5E70BCD7 FOREIGN KEY (owned_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BA217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE assignment ADD CONSTRAINT FK_30C544BAC1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE repeated_assignment ADD CONSTRAINT FK_FA68C0BC217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE repeated_assignment ADD CONSTRAINT FK_FA68C0BCC1DAFE35 FOREIGN KEY (seat_id) REFERENCES seat (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE seat ADD CONSTRAINT FK_3D5C3666FFA0C224 FOREIGN KEY (office_id) REFERENCES office (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE api_token_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE assignment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE office_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE person_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE repeated_assignment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE seat_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE api_token DROP CONSTRAINT FK_7BA2F5EB5E70BCD7');
        $this->addSql('ALTER TABLE assignment DROP CONSTRAINT FK_30C544BA217BBB47');
        $this->addSql('ALTER TABLE assignment DROP CONSTRAINT FK_30C544BAC1DAFE35');
        $this->addSql('ALTER TABLE repeated_assignment DROP CONSTRAINT FK_FA68C0BC217BBB47');
        $this->addSql('ALTER TABLE repeated_assignment DROP CONSTRAINT FK_FA68C0BCC1DAFE35');
        $this->addSql('ALTER TABLE seat DROP CONSTRAINT FK_3D5C3666FFA0C224');
        $this->addSql('DROP TABLE api_token');
        $this->addSql('DROP TABLE assignment');
        $this->addSql('DROP TABLE office');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE repeated_assignment');
        $this->addSql('DROP TABLE seat');
        $this->addSql('DROP TABLE "user"');
    }
}
