<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%group_assessment_grade}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%items}}`
 * - `{{%group_assessment}}`
 */
class m210829_220015_create_group_assessment_grade_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group_assessment_grade}}', [
            'id' => $this->primaryKey(),
            'mark' => $this->integer(3)->notNull(),
            'item_id' => $this->integer(11)->notNull(),
            'group_id'=> $this->integer(11)->notNull(),
        ]);

        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-group_assessment_grade-item_id}}',
            '{{%group_assessment_grade}}',
            'item_id'
        );

        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-group_assessment_grade-item_id}}',
            '{{%group_assessment_grade}}',
            'item_id',
            '{{%items}}',
            'id',
            'CASCADE'
        );

        // creates index for column `group_id`
        $this->createIndex(
            '{{%idx-group_assessment_grade-group_id}}',
            '{{%group_assessment_grade}}',
            'group_id'
        );

        // add foreign key for table `{{%group_assessment}}`
        $this->addForeignKey(
            '{{%fk-group_assessment_grade-group_id}}',
            '{{%group_assessment_grade}}',
            'group_id',
            '{{%group_assessment}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%group_assessment}}`
        $this->dropForeignKey(
            '{{%fk-group_assessment_grade-group_id}}',
            '{{%group_assessment_grade}}'
        );

        // drops index for column `group_id`
        $this->dropIndex(
            '{{%idx-group_assessment_grade-group_id}}',
            '{{%group_assessment_grade}}'
        );

        // drops foreign key for table `{{%items}}`
        $this->dropForeignKey(
            '{{%fk-group_assessment_grade-item_id}}',
            '{{%group_assessment_grade}}'
        );

        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-group_assessment_grade-item_id}}',
            '{{%group_assessment_grade}}'
        );

        $this->dropTable('{{%group_assessment_grade}}');
    }
}
