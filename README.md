Le block s’appelle body (ligne 51). Et je vois aussi que base.html.twig est minimaliste — c’est juste le shell HTML, pas de navbar/sidebar admin.
Voici les 3 templates corrigés à remplacer :
templates/admin/announcement_popup/index.html.twig

{% extends 'base.html.twig' %}

{% block body %}
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Announcement Popups</h1>
        <a href="{{ path('admin_announcement_popup_new') }}" class="btn btn-primary">+ Nouveau</a>
    </div>

    {% for message in app.flashes('success') %}
        <div class="alert alert-success">{{ message }}</div>
    {% endfor %}

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Priorité</th>
                <th>Actif</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            {% for popup in popups %}
            <tr>
                <td>{{ popup.title }}</td>
                <td>{{ popup.priority }}</td>
                <td>{{ popup.active ? '✅' : '❌' }}</td>
                <td>
                    <a href="{{ path('admin_announcement_popup_edit', {id: popup.id}) }}" class="btn btn-sm btn-secondary">Éditer</a>
                    <form method="post" action="{{ path('admin_announcement_popup_delete', {id: popup.id}) }}" style="display:inline">
                        <input type="hidden" name="_token" value="{{ csrf_token('delete_announcement_' ~ popup.id) }}">
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</button>
                    </form>
                </td>
            </tr>
            {% endfor %}
        </tbody>
    </table>
</div>
{% endblock %}


templates/admin/announcement_popup/new.html.twig

{% extends 'base.html.twig' %}

{% block body %}
<div class="container mt-4">
    <h1>Nouveau Popup</h1>
    <a href="{{ path('admin_announcement_popup_index') }}" class="btn btn-secondary mb-3">← Retour</a>
    {{ form_start(form) }}
        {{ form_widget(form) }}
        <button type="submit" class="btn btn-primary">Créer</button>
    {{ form_end(form) }}
</div>
{% endblock %}


templates/admin/announcement_popup/edit.html.twig

{% extends 'base.html.twig' %}

{% block body %}
<div class="container mt-4">
    <h1>Éditer : {{ popup.title }}</h1>
    <a href="{{ path('admin_announcement_popup_index') }}" class="btn btn-secondary mb-3">← Retour</a>
    {{ form_start(form) }}
        {{ form_widget(form) }}
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    {{ form_end(form) }}
</div>
{% endblock %}


Remplace les 3 fichiers, vide le cache (php bin/console cache:clear) et ça devrait marcher.​​​​​​​​​​​​​​​​