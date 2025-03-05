<?php

use Illuminate\Database\Schema\Blueprint;
use Laracache\Cache\Schema\Grammars\Grammar;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CacheSchemaGrammarTest extends TestCase
{
    public function tearDown(): void
    {
        m::close();
    }

    public function testBasicCreateTable()
    {
        $blueprint = new Blueprint('users');

        $blueprint->create();
        $blueprint->increments('id');
        $blueprint->string('email');

        $conn = $this->getConnection();

        $statements = $blueprint->toSql($conn, $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'create table users (id int not null identity primary key, email nvarchar(255) not null)',
            $statements[0]
        );
    }

    public function testBasicCreateTableWithReservedWords()
    {
        $blueprint = new Blueprint('users');

        $blueprint->create();
        $blueprint->increments('id');
        $blueprint->string('group');

        $conn = $this->getConnection();

        $statements = $blueprint->toSql($conn, $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'create table users (id int not null identity primary key, group nvarchar(255) not null)',
            $statements[0]
        );
    }

    public function testBasicCreateTableWithPrimary()
    {
        $blueprint = new Blueprint('users');

        $blueprint->create();
        $blueprint->integer('id')->primary();
        $blueprint->string('email');

        $conn = $this->getConnection();

        $statements = $blueprint->toSql($conn, $this->getGrammar());

        $this->assertEquals(2, \count($statements));
        $this->assertEquals(
            'create table users (id int not null, email nvarchar(255) not null)',
            $statements[0]
        );
        $this->assertEquals(
            'alter table users add constraint users_id_primary primary key (id)',
            $statements[1]
        );
    }

    public function testBasicCreateTableWithPrimaryAndForeignKeys()
    {
        $blueprint = new Blueprint('users');

        $blueprint->create();
        $blueprint->integer('id')->primary();
        $blueprint->string('email');
        $blueprint->integer('foo_id');
        $blueprint->foreign('foo_id')->references('id')->on('orders');

        $grammar = $this->getGrammar();
        $conn = $this->getConnection();

        $statements = $blueprint->toSql($conn, $grammar);

        $this->assertEquals(3, \count($statements));
        $this->assertEquals(
            'create table users (id int not null, email nvarchar(255) not null, foo_id int not null)',
            $statements[0]
        );
        $this->assertEquals(
            'alter table users add constraint users_foo_id_foreign foreign key (foo_id) references orders (id)',
            $statements[1]
        );
        $this->assertEquals(
            'alter table users add constraint users_id_primary primary key (id)',
            $statements[2]
        );
    }

    public function testBasicCreateTableWithDefaultValueAndIsNotNull()
    {
        $blueprint = new Blueprint('users');
        $blueprint->create();
        $blueprint->integer('id')->primary();
        $blueprint->string('email')->default('user@test.com');

        $conn = $this->getConnection();

        $statements = $blueprint->toSql($conn, $this->getGrammar());

        $this->assertEquals(2, \count($statements));
        $this->assertEquals(
            "create table users (id int not null, email nvarchar(255) not null default 'user@test.com')",
            $statements[0]
        );
        $this->assertEquals(
            'alter table users add constraint users_id_primary primary key (id)',
            $statements[1]
        );
    }

    public function testBasicAlterTable()
    {
        $blueprint = new Blueprint('users');
        $blueprint->integer('id');
        $blueprint->string('email');
        $blueprint->primary('id');

        $conn = $this->getConnection();
        $statements = $blueprint->toSql($conn, $this->getGrammar());

        $this->assertEquals(3, \count($statements));
        $this->assertEquals(
            'alter table users add id int not null',
            $statements[0]
        );
        $this->assertEquals(
            'alter table users add email nvarchar(255) not null',
            $statements[1]
        );
    }

    public function testBasicAlterTableWithPrimary()
    {
        $blueprint = new Blueprint('users');
        $blueprint->increments('id')->primary();
        $blueprint->string('email');

        $conn = $this->getConnection();

        $statements = $blueprint->toSql($conn, $this->getGrammar());

        $this->assertEquals(3, \count($statements));
        $this->assertEquals(
            'alter table users add id int not null identity',
            $statements[0]
        );
        $this->assertEquals(
            'alter table users add email nvarchar(255) not null',
            $statements[1]
        );
    }

    public function testDropTable()
    {
        $blueprint = new Blueprint('users');
        $blueprint->drop();

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals('drop table users', $statements[0]);
    }

    public function testDropColumn()
    {
        /**
         * Cache can only drop one column at a time.
         *
         * $blueprint->dropColumn('foo');
         * $blueprint->dropColumn('bar');
         */
        $blueprint = new Blueprint('users');
        $blueprint->dropColumn('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertStringContainsString('alter table users drop column foo', $statements[0]);
    }

    public function testDropPrimary()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropPrimary('foo_id_primary'); // name of constraints / index

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users drop constraint foo_id_primary',
            $statements[0]
        );
    }

    public function testDropUnique()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropUnique('users_foo_unique'); // name of constraints / index

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'drop index users_foo_unique on users',
            $statements[0]
        );
    }

    public function testDropIndex()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropIndex('users_foo_index');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals('drop index users_foo_index on users', $statements[0]);
    }

    public function testDropForeign()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dropForeign('users_foo_foreign');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users drop constraint users_foo_foreign',
            $statements[0]
        );
    }

    public function testAddingForeignKeyWithCascadeDelete()
    {
        $blueprint = new Blueprint('users');
        $blueprint->foreign('foo')
            ->references('id')
            ->on('orders')
            ->onDelete('cascade');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users add constraint users_foo_foreign foreign key (foo) references orders (id) on delete cascade',
            $statements[0]
        );
    }

    public function testAddingString()
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users add foo nvarchar(255) not null',
            $statements[0]
        );

        $blueprint = new Blueprint('users');
        $blueprint->string('foo', 100);

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users add foo nvarchar(100) not null',
            $statements[0]
        );

        $blueprint = new Blueprint('users');
        $blueprint->string('foo', 100)
            ->nullable()
            ->default('bar');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            "alter table users add foo nvarchar(100) null default 'bar'",
            $statements[0]
        );

        $blueprint = new Blueprint('users');
        $blueprint->string('foo', 100)
            ->nullable()
            ->default(new Illuminate\Database\Query\Expression('CURRENT_TIMESTAMP'));

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users add foo nvarchar(100) null default CURRENT_TIMESTAMP',
            $statements[0]
        );
    }

    public function testAddingLongTextMediumTextText()
    {
        $blueprint = new Blueprint('users');
        $blueprint->longText('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users add foo nvarchar(max) not null',
            $statements[0]
        );
    }

    public function testAddingChar()
    {
        $blueprint = new Blueprint('users');
        $blueprint->char('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users add foo nchar(255) not null',
            $statements[0]
        );

        $blueprint = new Blueprint('users');
        $blueprint->char('foo', 1);

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users add foo nchar(1) not null',
            $statements[0]
        );
    }

    public function testAddingBigInteger()
    {
        $blueprint = new Blueprint('users');
        $blueprint->bigInteger('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users add foo bigint not null',
            $statements[0]
        );

        $blueprint = new Blueprint('users');
        $blueprint->bigInteger('foo', true);

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users add foo bigint not null identity primary key',
            $statements[0]
        );
    }

    public function testAddingInteger()
    {
        $blueprint = new Blueprint('users');
        $blueprint->integer('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users add foo int not null',
            $statements[0]
        );

        $blueprint = new Blueprint('users');
        $blueprint->integer('foo', true);

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users add foo int not null identity primary key',
            $statements[0]
        );
    }

    public function testAddingMediumInteger()
    {
        $blueprint = new Blueprint('users');
        $blueprint->mediumInteger('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals('alter table users add foo int not null', $statements[0]);
    }

    public function testAddingTinyInteger()
    {
        $blueprint = new Blueprint('users');
        $blueprint->tinyInteger('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals('alter table users add foo tinyint not null', $statements[0]);
    }

    public function testAddingFloat()
    {
        $blueprint = new Blueprint('users');
        $blueprint->float('foo', 5, 2);

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals('alter table users add foo float(5) not null', $statements[0]);
    }

    public function testAddingDouble()
    {
        $blueprint = new Blueprint('users');
        $blueprint->double('foo', 5, 2);

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals('alter table users add foo double precision not null', $statements[0]);
    }

    public function testAddingDecimal()
    {
        $blueprint = new Blueprint('users');
        $blueprint->decimal('foo', 5, 2);

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals('alter table users add foo decimal(5, 2) not null', $statements[0]);
    }

    public function testAddingBoolean()
    {
        $blueprint = new Blueprint('users');
        $blueprint->boolean('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals('alter table users add foo bit not null', $statements[0]);
    }

    public function testAddingEnum()
    {
        $blueprint = new Blueprint('users');
        $blueprint->enum('foo', ['bar', 'baz']);

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            "alter table users add foo nvarchar(255) check (\"foo\" in (N'bar', N'baz')) not null",
            $statements[0]
        );
    }

    public function testAddingJson()
    {
        $blueprint = new Blueprint('users');
        $blueprint->json('fooj');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertEquals(
            'alter table users add fooj nvarchar(max) not null',
            $statements[0]
        );
    }

    public function testAddingJsonb()
    {
        $blueprint = new Blueprint('users');
        $blueprint->jsonb('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertEquals(
            'alter table users add foo nvarchar(max) not null',
            $statements[0]
        );
    }

    public function testAddingDate()
    {
        $blueprint = new Blueprint('users');
        $blueprint->date('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals('alter table users add foo date not null', $statements[0]);
    }

    public function testAddingDateTime()
    {
        $blueprint = new Blueprint('users');
        $blueprint->dateTime('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals('alter table users add foo datetime not null', $statements[0]);
    }

    public function testAddingTime()
    {
        $blueprint = new Blueprint('users');
        $blueprint->time('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals('alter table users add foo time not null', $statements[0]);
    }

    public function testAddingTimeStamp()
    {
        $blueprint = new Blueprint('users');
        $blueprint->timestamp('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals('alter table users add foo datetime not null', $statements[0]);
    }

    public function testAddingNullableTimeStamps()
    {
        $blueprint = new Blueprint('users');
        $blueprint->nullableTimestamps();

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(2, \count($statements));
        $this->assertEquals(
            'alter table users add created_at datetime null',
            $statements[0]
        );
    }

    public function testAddingUuid()
    {
        $blueprint = new Blueprint('users');
        $blueprint->uuid('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertCount(1, $statements);
        $this->assertEquals('alter table users add foo uniqueidentifier not null', $statements[0]);
    }

    public function testAddingBinary()
    {
        $blueprint = new Blueprint('users');
        $blueprint->binary('foo');

        $statements = $blueprint->toSql($this->getConnection(), $this->getGrammar());

        $this->assertEquals(1, \count($statements));
        $this->assertEquals(
            'alter table users add foo varbinary(max) not null',
            $statements[0]
        );
    }

    public function getGrammar()
    {
        return new Grammar();
    }

    protected function getConnection()
    {
        return m::mock('Illuminate\Database\Connection');
    }
}
