<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Basic TYPO3 Management Account model migrations
 */
class Version20121124232815 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("CREATE TABLE typo3_manager_domain_model_user (persistence_object_identifier VARCHAR(40) NOT NULL, preferences VARCHAR(40) DEFAULT NULL, UNIQUE INDEX UNIQ_76C0F0AE931A6F5 (preferences), PRIMARY KEY(persistence_object_identifier)) ENGINE = InnoDB");
		$this->addSql("CREATE TABLE typo3_usermanagement_domain_model_userpreferences (persistence_object_identifier VARCHAR(40) NOT NULL, preferences LONGTEXT NOT NULL COMMENT '(DC2Type:array)', PRIMARY KEY(persistence_object_identifier)) ENGINE = InnoDB");
		$this->addSql("ALTER TABLE typo3_usermanagement_domain_model_user ADD CONSTRAINT FK_76C0F0AE931A6F5 FOREIGN KEY (preferences) REFERENCES typo3_usermanagement_domain_model_userpreferences (persistence_object_identifier)");
		$this->addSql("ALTER TABLE typo3_usermanagement_domain_model_user ADD CONSTRAINT FK_76C0F0A47A46B0A FOREIGN KEY (persistence_object_identifier) REFERENCES typo3_party_domain_model_abstractparty (persistence_object_identifier) ON DELETE CASCADE");
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("ALTER TABLE typo3_usermanagement_domain_model_user DROP FOREIGN KEY FK_76C0F0AE931A6F5");
		$this->addSql("DROP TABLE typo3_usermanagement_domain_model_user");
		$this->addSql("DROP TABLE typo3_usermanagement_domain_model_userpreferences");
	}
}

?>