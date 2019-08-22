# Config-component: Subsettings for mysql-db

class settings_mysql:
    def __init__(self):
        # Initialize with empty values
        self.server = ""
        self.user = ""
        self.password = ""
        self.db = ""
