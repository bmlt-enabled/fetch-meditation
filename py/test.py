from JFTLanguage import JFTLanguage
from JFTSettings import JFTSettings
from JFT import JFT

for language in JFTLanguage:
    print(f"\n\n-=-=-=-=-=-=-=-= JFT - {language} -=-=-=-=-=-=-=-=\n\n")
    settings = JFTSettings(language)
    jft_instance = JFT.get_instance(settings)
    jft_entry = jft_instance.fetch()
    lang_name = jft_instance.get_language()
    # print(jft_entry.to_json())
    print(jft_entry.quote)
    print(f" -- {lang_name}")
