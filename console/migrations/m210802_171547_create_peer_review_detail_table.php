<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%peer_review_detail}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%items}}`
 * - `{{%peer_review}}`
 */
class m210802_171547_create_peer_review_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%peer_review_detail}}', [
            'id' => $this->primaryKey(),
            'item_id' => $this->integer(11)->notNull(),
            'mark' => $this->integer(3)->notNull(),
            'comment' => 'LONGTEXT',
            'peer_review_id' => $this->integer(11),
        ]);

        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-peer_review_detail-item_id}}',
            '{{%peer_review_detail}}',
            'item_id'
        );

        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-peer_review_detail-item_id}}',
            '{{%peer_review_detail}}',
            'item_id',
            '{{%items}}',
            'id',
            'CASCADE'
        );

        // creates index for column `peer_review_id`
        $this->createIndex(
            '{{%idx-peer_review_detail-peer_review_id}}',
            '{{%peer_review_detail}}',
            'peer_review_id'
        );

        // add foreign key for table `{{%peer_review}}`
        $this->addForeignKey(
            '{{%fk-peer_review_detail-peer_review_id}}',
            '{{%peer_review_detail}}',
            'peer_review_id',
            '{{%peer_review}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%items}}`
        $this->dropForeignKey(
            '{{%fk-peer_review_detail-item_id}}',
            '{{%peer_review_detail}}'
        );

        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-peer_review_detail-item_id}}',
            '{{%peer_review_detail}}'
        );

        // drops foreign key for table `{{%peer_review}}`
        $this->dropForeignKey(
            '{{%fk-peer_review_detail-peer_review_id}}',
            '{{%peer_review_detail}}'
        );

        // drops index for column `peer_review_id`
        $this->dropIndex(
            '{{%idx-peer_review_detail-peer_review_id}}',
            '{{%peer_review_detail}}'
        );

        $this->dropTable('{{%peer_review_detail}}');
    }
}
