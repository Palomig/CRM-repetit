#!/usr/bin/python3
import sys
import os

project_path = '/home/c/cw95865/public_html/tutor_crm'
venv_path = '/home/c/cw95865/venv'

sys.path.insert(0, project_path)
sys.path.insert(0, os.path.join(venv_path, 'lib/python3.6/site-packages'))

activate_this = os.path.join(venv_path, 'bin/activate_this.py')
if os.path.exists(activate_this):
    with open(activate_this) as f:
        exec(f.read(), {'__file__': activate_this})

os.environ['DJANGO_SETTINGS_MODULE'] = 'tutor_crm.settings'

from django.core.wsgi import get_wsgi_application
application = get_wsgi_application()