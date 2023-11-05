from JFTLanguage import JFTLanguage
from GermanJFT import GermanJFT
from EnglishJFT import EnglishJFT


class JFT:
    def __init__(self, settings):
        self.settings = settings

    def fetch(self):
        pass

    def get_language(self):
        pass

    @staticmethod
    def get_instance(settings):
        return {
            JFTLanguage.English: EnglishJFT,
            JFTLanguage.German: GermanJFT,
        }[settings.language](settings)
