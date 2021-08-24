<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%individual_feedback}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%items}}`
 * - `{{%peer_review}}`
 */
class m210815_220005_create_individual_feedback_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%individual_feedback}}', [
            'id' => $this->primaryKey(),
            'student_id' => $this->integer(11)->notNull(),
            'mark' => $this->integer(3),
            'comment' => 'LONGTEXT',
            'item_id' => $this->integer(11)->notNull(),
            'peer_review_id' => $this->integer(11)->notNull(),
        ]);

        // creates index for column `student_id`
        $this->createIndex(
            '{{%idx-individual_feedback-student_id}}',
            '{{%individual_feedback}}',
            'student_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-individual_feedback-student_id}}',
            '{{%individual_feedback}}',
            'student_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-individual_feedback-item_id}}',
            '{{%individual_feedback}}',
            'item_id'
        );

        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-individual_feedback-item_id}}',
            '{{%individual_feedback}}',
            'item_id',
            '{{%items}}',
            'id',
            'CASCADE'
        );

        // creates index for column `peer_review_id`
        $this->createIndex(
            '{{%idx-individual_feedback-peer_review_id}}',
            '{{%individual_feedback}}',
            'peer_review_id'
        );

        // add foreign key for table `{{%peer_review}}`
        $this->addForeignKey(
            '{{%fk-individual_feedback-peer_review_id}}',
            '{{%individual_feedback}}',
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
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-individual_feedback-student_id}}',
            '{{%individual_feedback}}'
        );

        // drops index for column `student_id`
        $this->dropIndex(
            '{{%idx-individual_feedback-student_id}}',
            '{{%individual_feedback}}'
        );

        // drops foreign key for table `{{%items}}`
        $this->dropForeignKey(
            '{{%fk-individual_feedback-item_id}}',
            '{{%individual_feedback}}'
        );

        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-individual_feedback-item_id}}',
            '{{%individual_feedback}}'
        );

        // drops foreign key for table `{{%peer_review}}`
        $this->dropForeignKey(
            '{{%fk-individual_feedback-peer_review_id}}',
            '{{%individual_feedback}}'
        );

        // drops index for column `peer_review_id`
        $this->dropIndex(
            '{{%idx-individual_feedback-peer_review_id}}',
            '{{%individual_feedback}}'
        );

        $this->dropTable('{{%individual_feedback}}');
    }
}
