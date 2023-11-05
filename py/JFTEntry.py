import json


class JFTEntry:
    def __init__(self, date, title, page, quote, source, content, thought, copyright):
        self.date = date
        self.title = title
        self.page = page
        self.quote = quote
        self.source = source
        self.content = content
        self.thought = thought
        self.copyright = copyright

    def to_json(self):
        return json.dumps(self.__dict__)
