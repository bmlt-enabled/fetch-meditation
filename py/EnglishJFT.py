from bs4 import BeautifulSoup
from utilities.HttpUtility import HttpUtility
from JFTEntry import JFTEntry


class EnglishJFT:
    def __init__(self, settings):
        self.settings = settings

    def get_language(self):
        return self.settings.language

    def fetch(self):
        url = 'https://www.jftna.org/jft/'
        data = HttpUtility.http_get(url)
        soup = BeautifulSoup(data, 'html.parser')
        td_elements = soup.find_all('td')
        jft_keys = ['date', 'title', 'page', 'quote', 'source', 'content', 'thought', 'copyright']
        result = {}

        for i, td in enumerate(td_elements):
            if jft_keys[i] == 'content':
                inner_html = ''.join(str(child) for child in td.children)
                result['content'] = [line.strip() for line in inner_html.split('<br/>') if line.strip()]
            else:
                result[jft_keys[i]] = td.text.strip()

        result["copyright"] = ' '.join(result["copyright"].split())

        return JFTEntry(
            result['date'],
            result['title'],
            result['page'],
            result['quote'],
            result['source'],
            result['content'],
            result['thought'],
            result['copyright']
        )
