<?php //declare(strict_types=1);
/**
 * This file is part of morpho-os/framework
 * It is distributed under the 'Apache License Version 2.0' license.
 * See the https://github.com/morpho-os/framework/blob/master/LICENSE for the full license text.
 */
namespace Morpho\Web;

use Morpho\Base\IFn;

/**
 * [RFC 3986](https://tools.ietf.org/html/rfc3986) compatible URI parser.
 */
class UriParser implements IFn {
    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @var array
     */
    protected $semiParsed;

    public function __invoke($uri): Uri {
        $this->uri = new Uri();

        # We use modified [regular expression from the RFC 3986](https://tools.ietf.org/html/rfc3986#appendix-B)
        if (!preg_match('~^
            ((?P<scheme>[^:/?\#]+):)?                      # scheme
            (?P<authority_>//(?P<authority>[^/?\#]*))?     # authority
            (?P<path>[^?\#]*)                              # path
            (?P<query_>\?(?P<query>[^\#]*))?               # query
            (?P<fragment_>\#(?P<fragment>.*))?             # fragment
            $~six', $uri, $match)) {
            throw new UriParseException('Invalid URI');
        }
        $this->semiParsed = $match;

        $this->parseScheme();
        $this->parseAuthority();
        $this->parsePath();
        $this->parseQuery();
        $this->parseFragment();

        return $this->uri;
    }
    
    protected function parseScheme(): void {
        $scheme = $this->semiParsed['scheme'];
        $this->uri->setScheme($scheme);
    }

    public static function parseOnlyAuthority(string $authority): array {
        // authority = [ userinfo "@" ] host [ ":" port ]
        if (!preg_match('~^
            (?P<userInfo_>(?P<userInfo>[^@]*)@)?
            (?P<host>(?:\[[^\]]+\]|[^:]+))
            (:(?P<port>\d+))?
            $~six', $authority, $authorityMatch)) {
            throw new UriParseException('Invalid authority');
        }
        $hasUserInfo = $authorityMatch['userInfo_'] !== '';
        return [
            'userInfo' => $hasUserInfo ? $authorityMatch['userInfo'] : null,
            'host' => $authorityMatch['host'],
            'port' => isset($authorityMatch['port']) ? (int)$authorityMatch['port'] : null,
        ];
    }

    protected function parseAuthority(): void {
        $hasAuthority = $this->semiParsed['authority_'] !== '';
        if ($hasAuthority) {
            $authority = $this->semiParsed['authority'];
            $this->uri->setAuthority($authority);
        }
    }

    protected function parsePath(): void {
        $path = $this->semiParsed['path'];
        $this->uri->setPath($path);
    }

    protected function parseQuery(): void {
        $hasQuery = isset($this->semiParsed['query_']) && $this->semiParsed['query_'] !== '';
        if ($hasQuery) {
            $this->uri->setQuery($this->semiParsed['query']);
        }

/*
3.4.  Query

   The query component contains non-hierarchical data that, along with
   data in the path component (Section 3.3), serves to identify a
   resource within the scope of the URI's scheme and naming authority
   (if any).  The query component is indicated by the first question
   mark ("?") character and terminated by a number sign ("#") character
   or by the end of the URI.



      query       = *( pchar / "/" / "?" )

   The characters slash ("/") and question mark ("?") may represent data
   within the query component.  Beware that some older, erroneous
   implementations may not handle such data correctly when it is used as
   the base URI for relative references (Section 5.1), apparently
   because they fail to distinguish query data from path data when
   looking for hierarchical separators.  However, as query components
   are often used to carry identifying information in the form of
   "key=value" pairs and one frequently used value is a reference to
   another URI, it is sometimes better for usability to avoid percent-
   encoding those characters.



 */
    }

    protected function parseFragment(): void {
        $hasFragment = isset($this->semiParsed['fragment_']) && $this->semiParsed['fragment_'] !== '';
        if ($hasFragment) {
            $this->uri->setFragment($this->semiParsed['fragment']);
        }
/*
3.5.  Fragment

   The fragment identifier component of a URI allows indirect
   identification of a secondary resource by reference to a primary
   resource and additional identifying information.  The identified
   secondary resource may be some portion or subset of the primary
   resource, some view on representations of the primary resource, or
   some other resource defined or described by those representations.  A
   fragment identifier component is indicated by the presence of a
   number sign ("#") character and terminated by the end of the URI.

      fragment    = *( pchar / "/" / "?" )

   The semantics of a fragment identifier are defined by the set of
   representations that might result from a retrieval action on the
   primary resource.  The fragment's format and resolution is therefore
   dependent on the media type [RFC2046] of a potentially retrieved
   representation, even though such a retrieval is only performed if the
   URI is dereferenced.  If no such representation exists, then the
   semantics of the fragment are considered unknown and are effectively
   unconstrained.  Fragment identifier semantics are independent of the
   URI scheme and thus cannot be redefined by scheme specifications.

   Individual media types may define their own restrictions on or
   structures within the fragment identifier syntax for specifying
   different types of subsets, views, or external references that are
   identifiable as secondary resources by that media type.  If the
   primary resource has multiple representations, as is often the case
   for resources whose representation is selected based on attributes of
   the retrieval request (a.k.a., content negotiation), then whatever is
   identified by the fragment should be consistent across all of those
   representations.  Each representation should either define the
   fragment so that it corresponds to the same secondary resource,
   regardless of how it is represented, or should leave the fragment
   undefined (i.e., not found).



   As with any URI, use of a fragment identifier component does not
   imply that a retrieval action will take place.  A URI with a fragment
   identifier may be used to refer to the secondary resource without any
   implication that the primary resource is accessible or will ever be
   accessed.

   Fragment identifiers have a special role in information retrieval
   systems as the primary form of client-side indirect referencing,
   allowing an author to specifically identify aspects of an existing
   resource that are only indirectly provided by the resource owner.  As
   such, the fragment identifier is not used in the scheme-specific
   processing of a URI; instead, the fragment identifier is separated
   from the rest of the URI prior to a dereference, and thus the
   identifying information within the fragment itself is dereferenced
   solely by the user agent, regardless of the URI scheme.  Although
   this separate handling is often perceived to be a loss of
   information, particularly for accurate redirection of references as
   resources move over time, it also serves to prevent information
   providers from denying reference authors the right to refer to
   information within a resource selectively.  Indirect referencing also
   provides additional flexibility and extensibility to systems that use
   URIs, as new media types are easier to define and deploy than new
   schemes of identification.

   The characters slash ("/") and question mark ("?") are allowed to
   represent data within the fragment identifier.  Beware that some
   older, erroneous implementations may not handle this data correctly
   when it is used as the base URI for relative references (Section
   5.1).
 */
    }
}