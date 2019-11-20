
- extensions: branch versioning
- add language mapping of all exts
- general language mapping of crowdin
- maori
- building: skip branch?
docs:
- language list
- language list in core
- should the language configuration fetched always before updating?
# errors

```
 [WARNING] Error with "reports" in "ro": Client error: `POST
           https://api.crowdin.com/api/project/typo3-cms/upload-translation?key=ad91ad9e2559b888ac2602ac81e77643`
           resulted in a `404 Not Found` response:
           <?xml version="1.0" encoding="UTF-8"?>
           <error>
             <code>17</code>
             <message>The specified directory was not found</messa (truncated...)
```


```
 27/39 [===================>--------]  69%
 [WARNING] Error with "scheduler" in "ro": Client error: `POST
           https://api.crowdin.com/api/project/typo3-cms/upload-translation?key=ad91ad9e2559b888ac2602ac81e77643`
           resulted in a `404 Not Found` response:
           <?xml version="1.0" encoding="UTF-8"?>
           <error>
             <code>8</code>
             <message>File was not found</message>
           </error>

```
