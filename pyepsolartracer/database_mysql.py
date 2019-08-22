# Database-Connector for MySQL/MariaDB

import mysql.connector


class db_mysql:
    def __init__(self, hostname, username, password, database, acommit=True):
        self.host = hostname
        self.user = username
        self.password = password
        self.database = database
        self.acommit = acommit
        self.establish_connection()  # Establish SQL-Connection

    def establish_connection(self):
        self.connection = mysql.connector.connect(
            host=self.host,
            user=self.user,
            passwd=self.password,
            db=self.database,
            autocommit=self.acommit  # Autocommit statements when their transactions complete
        )
        # self.connection.autocommit = True  # Autocommit statements when their transactions complete
        # print(self.connection)  # DEBUG

    def disconnect(self, cursor):
        if self.connection is not None:
            if cursor is not None:
                cursor.close()
            self.connection.close()

    def assure_sql_connection(self):
        # Checks if the connection is still alive and reconnects if not
        if not self.connection.is_connected():
            # Connection ist not established or lost in the meantime
            self.establish_connection()

    def execute_statement(self, statement):
        # Executes an SQL-Statement
        self.assure_sql_connection()
        mycursor = self.connection.cursor()
        mycursor.execute(statement)
        return mycursor

    def getresult(self, cursor):
        # Gets the result from a SQL-Cursor returned by a fired statement
        return cursor.fetchall()
