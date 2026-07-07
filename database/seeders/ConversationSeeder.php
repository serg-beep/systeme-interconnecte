<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Conversation, Entreprise, User, Message};

class ConversationSeeder extends Seeder {
    public function run() {
        $pharmacie = Entreprise::where('type', 'pharmacie')->first();
        $hopital   = Entreprise::where('type', 'hopital')->first();
        $labo      = Entreprise::where('type', 'laboratoire')->first();
        $user1     = User::where('email', 'moussa@sante.bf')->first();
        $user2     = User::where('email', 'aicha@sante.bf')->first();
        $user3     = User::where('email', 'ibrahim@sante.bf')->first();

        // Conversation privée
        $conv1 = Conversation::create(['sujet' => 'Commande médicaments', 'type' => 'prive']);
        $conv1->entreprises()->attach([$pharmacie->id, $hopital->id]);
        Message::create(['conversation_id' => $conv1->id, 'user_id' => $user2->id, 'contenu' => 'Bonjour, avez-vous du paracétamol en stock ?']);
        Message::create(['conversation_id' => $conv1->id, 'user_id' => $user1->id, 'contenu' => 'Oui, nous en avons 2000 comprimés disponibles.']);
        Message::create(['conversation_id' => $conv1->id, 'user_id' => $user2->id, 'contenu' => 'Parfait, nous allons passer une commande officielle.']);

        // Conversation de groupe
        $conv2 = Conversation::create(['sujet' => 'Coordination épidémie méningite', 'type' => 'groupe']);
        $conv2->entreprises()->attach([$pharmacie->id, $hopital->id, $labo->id]);
        Message::create(['conversation_id' => $conv2->id, 'user_id' => $user2->id, 'contenu' => 'Bonjour à tous, nous avons 5 cas suspects de méningite ce matin.']);
        Message::create(['conversation_id' => $conv2->id, 'user_id' => $user3->id, 'contenu' => 'Nous pouvons traiter les prélèvements en priorité. Envoyez-les dès que possible.']);
        Message::create(['conversation_id' => $conv2->id, 'user_id' => $user1->id, 'contenu' => 'Nous avons des antibiotiques en stock. Contactez-nous pour la commande.']);
    }
}
