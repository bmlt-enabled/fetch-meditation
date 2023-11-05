from bs4 import BeautifulSoup
from utilities.HttpUtility import HttpUtility
from JFTEntry import JFTEntry


class GermanJFT:
    def __init__(self, settings):
        self.settings = settings

    def get_language(self):
        return self.settings.language

    def fetch(self):
        url = 'https://narcotics-anonymous.de/artikel/nur-fuer-heute/'
        data = HttpUtility.http_get(url)
        soup = BeautifulSoup(data, 'html.parser')
        container = soup.find('div', {'id': 'jft-container'})
        result = {}

        for node in container.children:
            if node.name is not None:
                id = node.get('id')
                if id == 'jft-content':
                    content_list = []
                    for contentNode in node.find_all('p'):
                        content_list.append(contentNode.get_text(strip=True).replace('\n', ' '))
                    result[id] = content_list
                else:
                    result[id] = node.get_text(strip=True).replace('\n', '')
        result['page'] = ''
        result['copyright'] = ''
        result['jft-content'] = list(filter(None, result['jft-content']))

        return JFTEntry(
            result['jft-date'],
            result['jft-title'],
            result['page'],
            result['jft-quote'],
            result['jft-quote-source'],
            result['jft-content'],
            result['jft-thought'],
            result['copyright']
        )
